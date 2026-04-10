let faceApiLoader = null

import FaceService from './FaceServices'

// Expose globally so Alpine x-data can find it without timing issues
window.formCache = function () {
    return {
        preview: null,
        stream: null,
        streaming: false,
        submitting: false,
        isLoading: false,
        face: null,
        detectionState: 'idle', // idle | searching | no_face | dark | ready | captured
        detectionMessage: null,
        detectTimer: null,
        brightnessThreshold: 100,
        detectionProgress: 0,
        identifying: false,
        modelsReady: false,
        minDetectionScore: 0.60,

        async init() {
            console.log('formCache init - isLoading:', this.isLoading);
            const form = document.querySelector('form')
            const saved = JSON.parse(localStorage.getItem('register_cache') || '{}')

            // Wait for face-api global to exist (loaded via CDN in Blade)
            await this.waitForFaceApi()

            // Restore cached text/select values
            Object.entries(saved).forEach(([name, value]) => {
                if (name === 'profile_photo_base64') return
                const input = form.querySelector(`[name="${name}"]`)
                const isSelect = input?.tagName === 'SELECT'
                if (input && input.type !== 'file' && input.type !== 'password' && (!input.value || isSelect)) {
                    input.value = value
                    input.dispatchEvent(new Event('input'))
                }
            })

            // Restore cached photo
            this.restorePhoto(saved.profile_photo_base64, form)

            // Cache normal inputs on change
            form.querySelectorAll('input, select, textarea').forEach(input => {
                if (input.type === 'file' || input.type === 'password') return
                input.addEventListener('input', () => {
                    const cache = JSON.parse(localStorage.getItem('register_cache') || '{}')
                    cache[input.name] = input.value
                    localStorage.setItem('register_cache', JSON.stringify(cache))
                })
            })

            // Init face service
            const video = this.$refs.video
            if (!video) return
            this.face = new FaceService(video, { modelPath: '/models', brightnessThreshold: 80 })
            await this.face.loadModels()
            this.modelsReady = true
        },

        async waitForFaceApi(retries = 50) {
            if (window.faceapi) return
            if (!faceApiLoader) {
                faceApiLoader = new Promise((resolve, reject) => {
                    const existing = document.querySelector('script[data-faceapi-cdn]')
                    if (existing) {
                        existing.addEventListener('load', () => resolve(window.faceapi))
                        existing.addEventListener('error', () => reject(new Error('faceapi cdn failed')))
                        return
                    }
                    const s = document.createElement('script')
                    s.src = 'https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js'
                    s.async = true
                    s.dataset.faceapiCdn = 'true'
                    s.onload = () => resolve(window.faceapi)
                    s.onerror = () => reject(new Error('faceapi cdn failed'))
                    document.head.appendChild(s)
                })
            }
            await faceApiLoader
            if (!window.faceapi) {
                throw new Error('faceapi not loaded')
            }
        },

        restorePhoto(base64, form) {
            if (!base64) return
            this.preview = base64
            const dt = new DataTransfer()
            const byteString = atob(base64.split(',')[1])
            const ab = new ArrayBuffer(byteString.length)
            const ia = new Uint8Array(ab)
            for (let i = 0; i < byteString.length; i++) ia[i] = byteString.charCodeAt(i)
            const blob = new Blob([ab], { type: 'image/jpeg' })
            const file = new File([blob], 'profile_photo.jpg', { type: 'image/jpeg' })
            dt.items.add(file)
            const uploadInput = form.querySelector("[name='profile_photo']")
            if (uploadInput) uploadInput.files = dt.files
        },

        handlePhoto(event) {
            const file = event.target.files[0]
            if (!file) return
            this.cachePhoto(file)
        },

        cachePhoto(file) {
            const reader = new FileReader()
            reader.onload = (e) => {
                this.preview = e.target.result
                const cache = JSON.parse(localStorage.getItem('register_cache') || '{}')
                cache.profile_photo_base64 = e.target.result
                localStorage.setItem('register_cache', JSON.stringify(cache))
            }
            reader.readAsDataURL(file)
        },

        async startCamera() {
            if (!this.face || !this.modelsReady) {
                this.detectionMessage = 'Loading face detection… please wait.'
                return
            }
            try {
                await this.face.startCamera()
                this.stream = this.face.stream
                this.streaming = this.face.streaming
                this.detectionState = 'searching'
                this.detectionMessage = 'Center your face in the circle, then tap Capture.'
                this.detectionProgress = 0
                this.startDetectionLoop()
            } catch (e) {
                console.error(e)
                this.streaming = false
            }
        },

        chooseUpload() {
            this.stopCamera()
            this.$refs.uploadInput?.click()
        },

        async captureFrame() {
            if (!this.modelsReady) {
                this.detectionMessage = 'Loading face detection… please wait.'
                return
            }
            if (this.identifying) return
            this.identifying = true
            this.detectionState = 'identifying'
            this.detectionMessage = 'Identifying…'
            try {
                const blob = await this.face.captureRawFrame()
                if (!blob) {
                    this.detectionState = 'recapture'
                    this.detectionMessage = 'Capture failed. Please try again.'
                    return
                }
                const eligibility = await this.checkEligibility()
                if (!eligibility) {
                    this.detectionState = 'recapture'
                    this.detectionMessage = this.detectionMessage || 'Face not ready. Please recenter and try again.'
                    return
                }
                const file = new File([blob], 'profile_photo.jpg', { type: 'image/jpeg' })
                const dt = new DataTransfer()
                dt.items.add(file)
                if (this.$refs.uploadInput) this.$refs.uploadInput.files = dt.files
                this.cachePhoto(file)
                this.stopCamera()
                this.detectionState = 'captured'
                this.detectionMessage = null
                this.detectionProgress = 0
            } finally {
                this.identifying = false
            }
        },

        startDetectionLoop() {
            this.stopDetectionLoop()
            this.detectTimer = setInterval(() => this.runDetectionTick(), 600)
        },

        stopDetectionLoop() {
            if (this.detectTimer) {
                clearInterval(this.detectTimer)
                this.detectTimer = null
            }
            this.detectionProgress = 0
        },

        async runDetectionTick() {
            if (this.identifying) return
            if (!this.streaming || !this.face) return
            const eligible = await this.checkEligibility(true)
            if (!eligible) return

            this.detectionState = 'ready'
            this.detectionMessage = 'Face looks good. Tap Capture.'
            this.detectionProgress = 0
        },

        async checkEligibility(fromTick = false) {
            const detection = await this.face.detectFace()
            if (!detection) {
                this.detectionState = 'no_face'
                this.detectionMessage = 'No face detected. Please center your face.'
                this.detectionProgress = Math.max(0, this.detectionProgress - 8)
                return false
            }

            if (detection.score && detection.score < this.minDetectionScore) {
                this.detectionState = 'no_face'
                this.detectionMessage = 'Face not clear. Hold steady.'
                this.detectionProgress = Math.max(0, this.detectionProgress - 2)
                return false
            }

            const brightness = this.face.checkLighting()
            if (brightness < this.brightnessThreshold) {
                this.detectionState = 'dark'
                this.detectionMessage = 'Lighting too low. Move to a brighter spot.'
                this.detectionProgress = Math.max(0, this.detectionProgress - 5)
                return false
            }

            const brightnessOk = brightness >= this.brightnessThreshold + 3

            const { box } = detection
            const video = this.face.video
            const ratioW = box.width / (video?.videoWidth || 1)
            const ratioH = box.height / (video?.videoHeight || 1)
            const faceRatio = Math.max(ratioW, ratioH)
            const minRatio = 0.35
            const maxRatio = 0.50

            if (faceRatio < minRatio) {
                this.detectionState = 'too_far'
                this.detectionMessage = 'Move closer so your face fills the circle.'
                this.detectionProgress = Math.max(0, this.detectionProgress - 5)
                return false
            }

            if (faceRatio > maxRatio) {
                this.detectionState = 'too_close'
                this.detectionMessage = 'Move back slightly so your face fits the circle.'
                this.detectionProgress = Math.max(0, this.detectionProgress - 5)
                return false
            }

            const faceCenterX = (box.x + box.width / 2) / (video?.videoWidth || 1)
            const faceCenterY = (box.y + box.height / 2) / (video?.videoHeight || 1)
            const dx = Math.abs(faceCenterX - 0.5)
            const dy = Math.abs(faceCenterY - 0.6) // slight downward bias to counter top-heavy framing
            const dxWeight = 1.1
            const dyWeight = 1.2 // tighter vertical tolerance without biasing upward
            // Tie centering to the visible circle: face center plus its radius must remain within the circle (with a tighter margin)
            const faceRadius = faceRatio / 2
            const circleRadius = 0.5
            const margin = 0.0
            const maxCenterDistance = Math.max(0.005, circleRadius - margin - faceRadius)
            const centerDistance = Math.hypot(dx * dxWeight, dy * dyWeight)
            const strictDistance = maxCenterDistance * 0.15 
            if (centerDistance > strictDistance) {
                this.detectionState = 'off_center'
                this.detectionMessage = 'Center your face in the guide.'
                this.detectionProgress = 0
                return false
            }

            // update shared brightnessOk if called from tick
            if (fromTick) {
                this.brightnessOk = brightnessOk
            }
            return brightnessOk
        },

        stopCamera() {
            this.face?.stopCamera()
            this.stream = null
            this.streaming = false
            this.stopDetectionLoop()
            this.detectionState = 'idle'
            this.detectionMessage = null
            this.detectionProgress = 0
        },

        clear() {
            this.preview = null
            const uploadInput = this.$refs.uploadInput
            if (uploadInput) uploadInput.value = ''
            this.stopCamera()
            const cache = JSON.parse(localStorage.getItem('register_cache') || '{}')
            delete cache.profile_photo_base64
            localStorage.setItem('register_cache', JSON.stringify(cache))
        }
    }
}

// Ensure Alpine picks it up whether it starts before or after this script
if (window.Alpine) {
    window.Alpine.data('formCache', window.formCache)
}
document.addEventListener('alpine:init', () => {
    window.Alpine?.data('formCache', window.formCache)
})

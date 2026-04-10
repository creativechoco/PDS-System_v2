export default class FaceService {
    constructor(videoElement, options = {}) {
        this.video = videoElement
        this.streaming = false
        this.stream = null

        // Options
        this.brightnessThreshold = options.brightnessThreshold || 80
        this.modelPath = options.modelPath || "/models"
        this.detectionOptions = new faceapi.TinyFaceDetectorOptions()
    }

    async loadModels() {
        await faceapi.nets.tinyFaceDetector.loadFromUri(this.modelPath)
        console.log("✅ Face detector loaded")
    }

    async startCamera() {
        try {
            this.stopCamera()
            this.stream = await navigator.mediaDevices.getUserMedia({ video: true })
            this.video.srcObject = this.stream
            await this.video.play()
            this.streaming = true
        } catch (e) {
            console.error("Camera failed:", e)
            this.streaming = false
        }
    }

    stopCamera() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop())
        }
        this.streaming = false
    }

    async detectFace() {
        if (!this.streaming) return null
        return await faceapi.detectSingleFace(this.video, this.detectionOptions)
    }

    checkLighting() {
        if (!this.streaming) return 0
        const canvas = document.createElement("canvas")
        canvas.width = this.video.videoWidth
        canvas.height = this.video.videoHeight
        const ctx = canvas.getContext("2d")
        ctx.drawImage(this.video, 0, 0)
        const data = ctx.getImageData(0, 0, canvas.width, canvas.height).data
        let brightness = 0
        for (let i = 0; i < data.length; i += 4) {
            brightness += (data[i] + data[i + 1] + data[i + 2]) / 3
        }
        brightness /= data.length / 4
        return brightness
    }

    async captureRawFrame() {
        if (!this.streaming) return null
        const canvas = document.createElement("canvas")
        canvas.width = this.video.videoWidth
        canvas.height = this.video.videoHeight
        const ctx = canvas.getContext("2d")
        // Flip horizontally so capture matches the unmirrored preview
        ctx.translate(canvas.width, 0)
        ctx.scale(-1, 1)
        ctx.drawImage(this.video, 0, 0, canvas.width, canvas.height)
        return await new Promise(resolve => {
            canvas.toBlob(blob => resolve(blob), "image/jpeg", 0.9)
        })
    }

    async captureIfValid() {
        const detection = await this.detectFace()
        if (!detection) {
            return null
        }

        const brightness = this.checkLighting()
        if (brightness < this.brightnessThreshold) {
            return null
        }

        return await this.captureRawFrame()
    }
}
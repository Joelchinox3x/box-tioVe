if (!window.ImageOptimizer) {
    const ImageOptimizer = {
        /**
         * Comprime y convierte una imagen a WebP
         */
        optimize: function (file, options = {}) {
            const quality = options.quality || 0.8;
            const maxWidth = options.maxWidth || 1200;

            return new Promise((resolve, reject) => {
                if (!file.type.match(/image.*/)) {
                    reject(new Error("El archivo no es una imagen"));
                    return;
                }

                if (file.type === 'image/webp' || file.type === 'image/x-webp') {
                    resolve(file);
                    return;
                }

                const reader = new FileReader();
                reader.readAsDataURL(file);

                reader.onload = (event) => {
                    const img = new Image();
                    img.src = event.target.result;

                    img.onload = () => {
                        let width = img.width;
                        let height = img.height;

                        if (width > maxWidth) {
                            height = Math.round(height * (maxWidth / width));
                            width = maxWidth;
                        }

                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;

                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);

                        canvas.toBlob((blob) => {
                            if (blob) {
                                const newName = file.name.replace(/\.[^/.]+$/, "") + ".webp";
                                const optimizedFile = new File([blob], newName, {
                                    type: "image/webp",
                                    lastModified: Date.now()
                                });
                                resolve(optimizedFile);
                            } else {
                                reject(new Error("Error al generar Blob"));
                            }
                        }, 'image/webp', quality);
                    };
                    img.onerror = (e) => reject(e);
                };
                reader.onerror = (e) => reject(e);
            });
        }
    };

    window.ImageOptimizer = ImageOptimizer;
}
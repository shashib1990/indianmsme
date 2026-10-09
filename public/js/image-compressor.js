document.addEventListener('DOMContentLoaded', () => {
    // Inject custom CSS for compression spinner/feedback
    const style = document.createElement('style');
    style.innerHTML = `
        .compressing-overlay {
            position: absolute;
            background: rgba(255, 255, 255, 0.9);
            color: #212529;
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 4px;
            border: 1px solid #ced4da;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            pointer-events: none;
            z-index: 999;
            margin-left: 10px;
            font-weight: 500;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .compressing-overlay .spinner {
            width: 12px;
            height: 12px;
            border: 2px solid #0d6efd;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: comp-spin 0.8s linear infinite;
        }
        @keyframes comp-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);

    // Global listener for file input change
    document.body.addEventListener('change', async (event) => {
        const input = event.target;
        if (input.tagName !== 'INPUT' || input.type !== 'file') return;

        // Check if input or form has data-no-compress attribute
        if (input.dataset.noCompress === 'true' || input.hasAttribute('data-no-compress') || input.closest('[data-no-compress="true"]')) {
            return;
        }

        // If this event was triggered by our re-dispatch, let it pass through
        if (input.dataset.isCompressing === 'done') {
            delete input.dataset.isCompressing;
            return;
        }

        const files = Array.from(input.files || []);
        if (files.length === 0) return;

        const imagesToCompress = files.filter(file => file.type.startsWith('image/'));
        if (imagesToCompress.length === 0) return;

        // Stop other change listeners from running until we finish compression
        event.stopImmediatePropagation();
        event.preventDefault();

        // Find enclosing form to disable submit button
        const form = input.closest('form');
        const submitButtons = form ? form.querySelectorAll('button[type="submit"], input[type="submit"]') : [];

        // Create status badge near input
        const statusBadge = document.createElement('span');
        statusBadge.className = 'compressing-overlay';
        statusBadge.innerHTML = '<div class="spinner"></div> Optimizing image...';
        
        // Position status badge
        input.parentNode.style.position = 'relative';
        input.after(statusBadge);

        // Disable submit buttons to prevent submission during compression
        submitButtons.forEach(btn => btn.disabled = true);

        try {
            const newFilesList = new DataTransfer();
            for (const file of files) {
                if (file.type.startsWith('image/')) {
                    const compressedFile = await compressImage(file);
                    newFilesList.items.add(compressedFile);
                } else {
                    newFilesList.items.add(file);
                }
            }

            input.files = newFilesList.files;
            input.dataset.isCompressing = 'done';
            
            // Re-trigger the change event so inline previews and other handlers run
            const changeEvent = new Event('change', { bubbles: true });
            input.dispatchEvent(changeEvent);

        } catch (err) {
            console.error('Image compression error:', err);
        } finally {
            statusBadge.remove();
            submitButtons.forEach(btn => {
                // Only enable if the button didn't have disabled attribute pre-existing
                if (btn.getAttribute('data-originally-disabled') !== 'true') {
                    btn.disabled = false;
                }
            });
        }
    });

    // Helper function to compress image using HTML5 canvas while preserving PNG/WebP alpha transparency
    function compressImage(file) {
        return new Promise((resolve) => {
            const mimeType = (file.type || '').toLowerCase();
            const fileName = (file.name || '').toLowerCase();

            const isPng = mimeType === 'image/png' || fileName.endsWith('.png');
            const isWebp = mimeType === 'image/webp' || fileName.endsWith('.webp');
            const isSvg = mimeType === 'image/svg+xml' || fileName.endsWith('.svg');
            const isGif = mimeType === 'image/gif' || fileName.endsWith('.gif');
            const isIco = mimeType === 'image/x-icon' || mimeType === 'image/vnd.microsoft.icon' || fileName.endsWith('.ico');

            // Do not alter vector, animated, or icon formats
            if (isSvg || isGif || isIco) {
                resolve(file);
                return;
            }

            // Determine output mime type and extension
            let outputMime = 'image/jpeg';
            let extension = '.jpg';
            let quality = 0.85;

            if (isPng) {
                outputMime = 'image/png';
                extension = '.png';
            } else if (isWebp) {
                outputMime = 'image/webp';
                extension = '.webp';
                quality = 0.85;
            }

            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = (event) => {
                const img = new Image();
                img.src = event.target.result;
                img.onload = () => {
                    let width = img.width;
                    let height = img.height;

                    const maxDimension = 1600;
                    // If image is already smaller than maxDimension and is PNG/WebP under 2MB, keep original file
                    if (width <= maxDimension && height <= maxDimension && (isPng || isWebp) && file.size < 2 * 1024 * 1024) {
                        resolve(file);
                        return;
                    }

                    if (width > maxDimension || height > maxDimension) {
                        if (width > height) {
                            height = Math.round((height * maxDimension) / width);
                            width = maxDimension;
                        } else {
                            width = Math.round((width * maxDimension) / height);
                            height = maxDimension;
                        }
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;

                    const ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, width, height);
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob((blob) => {
                        if (!blob) {
                            resolve(file); // fallback to original file if blob fails
                            return;
                        }
                        const name = file.name.replace(/\.[^/.]+$/, "") + extension;
                        const compressedFile = new File([blob], name, {
                            type: outputMime,
                            lastModified: Date.now()
                        });
                        resolve(compressedFile);
                    }, outputMime, quality);
                };
                img.onerror = () => {
                    resolve(file); // fallback to original file
                };
            };
            reader.onerror = () => {
                resolve(file); // fallback to original file
            };
        });
    }
});

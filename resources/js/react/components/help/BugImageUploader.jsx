import { useRef } from "react";

const MAX_IMAGES = 2;
const MAX_SIZE_BYTES = 2 * 1024 * 1024;
const ACCEPTED_TYPES = ["image/png", "image/jpeg", "image/webp"];

export default function BugImageUploader({ images, onChange, onError }) {
    const inputRef = useRef(null);

    const handleFiles = (fileList) => {
        onError("");

        const remainingSlots = MAX_IMAGES - images.length;
        const files = Array.from(fileList).slice(0, remainingSlots);
        const accepted = [];

        files.forEach((file) => {
            if (!ACCEPTED_TYPES.includes(file.type)) {
                onError(`${file.name} isn't a supported image type (PNG, JPG or WEBP).`);
                return;
            }
            if (file.size > MAX_SIZE_BYTES) {
                onError(`${file.name} is larger than 2 MB.`);
                return;
            }
            accepted.push({
                id: `${file.name}-${file.lastModified}-${file.size}`,
                file,
                previewUrl: URL.createObjectURL(file),
            });
        });

        if (accepted.length > 0) {
            onChange([...images, ...accepted]);
        }
    };

    const handleInputChange = (event) => {
        if (event.target.files?.length) handleFiles(event.target.files);
        event.target.value = "";
    };

    const removeImage = (id) => {
        const target = images.find((image) => image.id === id);
        if (target) URL.revokeObjectURL(target.previewUrl);
        onChange(images.filter((image) => image.id !== id));
    };

    return (
        <div className="akd-bug-images">
            <p className="akd-bug-images__label">
                Attachments ({images.length}/{MAX_IMAGES})
            </p>

            <div className="akd-bug-images__grid">
                {images.map((image) => (
                    <div className="akd-bug-images__preview" key={image.id}>
                        <img src={image.previewUrl} alt="" />
                        <button
                            type="button"
                            className="akd-bug-images__remove"
                            onClick={() => removeImage(image.id)}
                            aria-label={`Remove ${image.file.name}`}
                        >
                            <i className="fa-solid fa-xmark" aria-hidden="true" />
                        </button>
                    </div>
                ))}

                {images.length < MAX_IMAGES && (
                    <button
                        type="button"
                        className="akd-bug-images__add"
                        onClick={() => inputRef.current?.click()}
                    >
                        <i className="fa-solid fa-plus" aria-hidden="true" />
                        Add image
                    </button>
                )}
            </div>

            <input
                ref={inputRef}
                type="file"
                accept={ACCEPTED_TYPES.join(",")}
                multiple
                hidden
                onChange={handleInputChange}
            />
        </div>
    );
}
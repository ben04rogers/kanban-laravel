import { useRef, useState } from 'react';
import { router, usePage } from '@inertiajs/react';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';

export default function UpdateProfilePhotoForm({ className = '' }) {
    const { auth } = usePage().props;
    const user = auth.user;
    const [photoPreview, setPhotoPreview] = useState(null);
    const [processing, setProcessing] = useState(false);
    const [errors, setErrors] = useState({});
    const photoInput = useRef();

    const selectNewPhoto = () => {
        photoInput.current.click();
    };

    const updatePhotoPreview = () => {
        const photo = photoInput.current.files[0];

        if (!photo) return;

        const reader = new FileReader();

        reader.onload = (e) => {
            setPhotoPreview(e.target.result);
        };

        reader.readAsDataURL(photo);
    };

    const uploadPhoto = () => {
        if (!photoInput.current.files[0]) return;

        const formData = new FormData();
        formData.append('photo', photoInput.current.files[0]);

        setProcessing(true);
        setErrors({});

        router.post(route('profile.photo.update'), formData, {
            preserveScroll: true,
            onSuccess: () => {
                setPhotoPreview(null);
                setProcessing(false);
                photoInput.current.value = '';
            },
            onError: (errors) => {
                setErrors(errors);
                setProcessing(false);
            },
        });
    };

    const deletePhoto = () => {
        router.delete(route('profile.photo.delete'), {
            preserveScroll: true,
        });
    };

    const clearPhotoSelection = () => {
        setPhotoPreview(null);
        photoInput.current.value = '';
    };

    return (
        <section className={className}>
            <header>
                <h2 className="text-lg font-medium text-gray-900">
                    Profile Photo
                </h2>

                <p className="mt-1 text-sm text-gray-600">
                    Add or update your profile photo.
                </p>
            </header>

            <div className="mt-6 space-y-6">
                {/* Current Photo */}
                <div>
                    <div className="mt-2 flex items-center gap-4">
                        {/* Show photo preview if selecting new photo */}
                        {photoPreview ? (
                            <img
                                src={photoPreview}
                                alt="Profile photo preview"
                                className="h-20 w-20 rounded-full object-cover"
                            />
                        ) : user.profile_photo_url ? (
                            <img
                                src={user.profile_photo_url}
                                alt={user.name}
                                className="h-20 w-20 rounded-full object-cover"
                            />
                        ) : (
                            <div className="flex h-20 w-20 items-center justify-center rounded-full bg-blue-500 text-2xl font-medium text-white">
                                {user.name.charAt(0).toUpperCase()}
                            </div>
                        )}

                        <div className="flex gap-2">
                            <SecondaryButton type="button" onClick={selectNewPhoto}>
                                Select New Photo
                            </SecondaryButton>

                            {user.profile_photo_url && !photoPreview && (
                                <SecondaryButton
                                    type="button"
                                    onClick={deletePhoto}
                                    className="text-red-600"
                                >
                                    Remove Photo
                                </SecondaryButton>
                            )}

                            {photoPreview && (
                                <SecondaryButton
                                    type="button"
                                    onClick={clearPhotoSelection}
                                >
                                    Cancel
                                </SecondaryButton>
                            )}
                        </div>
                    </div>

                    <input
                        type="file"
                        ref={photoInput}
                        className="hidden"
                        accept="image/*"
                        onChange={updatePhotoPreview}
                    />

                    <InputError message={errors.photo} className="mt-2" />
                </div>

                {/* Upload Button */}
                {photoPreview && (
                    <div className="flex items-center gap-4">
                        <PrimaryButton onClick={uploadPhoto} disabled={processing}>
                            Upload Photo
                        </PrimaryButton>
                    </div>
                )}
            </div>
        </section>
    );
}

export default function UserAvatar({ user, size = 'md' }) {
    const sizeClasses = {
        xs: 'w-5 h-5 text-xs',
        sm: 'w-6 h-6 text-xs',
        md: 'w-8 h-8 text-sm',
        lg: 'w-12 h-12 text-base',
        xl: 'w-20 h-20 text-2xl',
    };

    const className = `${sizeClasses[size]} rounded-full flex items-center justify-center text-white font-medium`;

    if (user.profile_photo_url) {
        return (
            <img
                src={user.profile_photo_url}
                alt={user.name}
                className={`${sizeClasses[size]} rounded-full object-cover`}
            />
        );
    }

    return (
        <div className={`${className} bg-blue-500`}>
            {user.name.charAt(0).toUpperCase()}
        </div>
    );
}

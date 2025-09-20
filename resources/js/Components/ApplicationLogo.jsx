export default function ApplicationLogo(props) {
    return (
        <div 
            {...props}
            className="flex items-center justify-center"
        >
            <span className="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                Velocity
            </span>
        </div>
    );
}

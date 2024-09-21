import React from "react";
import "@/Components/ErrorDisplay/styles.css";
import { ErrorDisplayProps } from "@/Components/ErrorDisplay/types";

const ErrorDisplay: React.FC<ErrorDisplayProps> = ({ errors }) => {
    return (
        <div className="error-messages">
            {Object.entries(errors).map(([field, messages]) => (
                <div key={field}>
                    {messages.map((message, index) => (
                        <p key={index} className="error-message">
                            {message}
                        </p>
                    ))}
                </div>
            ))}
        </div>
    );
};

export default ErrorDisplay;

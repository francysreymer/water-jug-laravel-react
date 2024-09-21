import React, { useState } from "react";
import "@/Components/WaterJugForm/styles.css";
import { WaterJugFormProps } from "@/Components/WaterJugForm/types";

const WaterJugForm: React.FC<WaterJugFormProps> = ({ onSubmit }) => {
    const [x, setX] = useState<number | string>("");
    const [y, setY] = useState<number | string>("");
    const [z, setZ] = useState<number | string>("");

    const handleSubmit = () => {
        if (x !== "" && y !== "" && z !== "") {
            onSubmit(Number(x), Number(y), Number(z)); // Convert values to numbers
        }
    };

    return (
        <form className="water-jug-form" onSubmit={handleSubmit}>
            <div className="form-group">
                <label>Bucket X:</label>
                <input
                    type="number"
                    value={x}
                    onChange={(e) => setX(Number(e.target.value))}
                />
            </div>
            <div className="form-group">
                <label>Bucket Y:</label>
                <input
                    type="number"
                    value={y}
                    onChange={(e) => setY(Number(e.target.value))}
                />
            </div>
            <div className="form-group">
                <label>Amount Wanted Z:</label>
                <input
                    type="number"
                    value={z}
                    onChange={(e) => setZ(Number(e.target.value))}
                />
            </div>
            <button type="button" onClick={handleSubmit}>
                Solve
            </button>
        </form>
    );
};

export default WaterJugForm;

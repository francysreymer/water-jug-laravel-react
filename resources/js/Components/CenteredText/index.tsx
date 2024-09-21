import React from "react";
import { CenteredTextProps } from "@/Components/CenteredText/types";
import "@/Components/CenteredText/styles.css";

const CenteredText: React.FC<CenteredTextProps> = ({ text }) => {
    return <h1 className="centered-text">{text}</h1>;
};

export default CenteredText;

import React from "react";
import {
    SolutionsState,
    SolutionStep,
} from "@/Components/SolutionDisplay/types";
import "@/Components/SolutionDisplay/styles.css";

const SolutionDisplay: React.FC<SolutionsState> = ({
    bestSolution,
    worstSolution,
}) => {
    const renderSolutionTable = (title: string, solutions: SolutionStep[]) => (
        <div className="solution-container">
            <h2 className="solution-header-highlight">{title}</h2>
            <table className="solution-table">
                <thead>
                    <tr>
                        <th>Bucket X</th>
                        <th>Bucket Y</th>
                        <th>Explanation</th>
                    </tr>
                </thead>
                <tbody>
                    {solutions.map((step) => (
                        <tr key={step.step}>
                            <td>{step.bucketX}</td>
                            <td>{step.bucketY}</td>
                            <td>
                                {step.action}
                                {step.status && (
                                    <span className="solution-status">
                                        {" "}
                                        - {step.status.toUpperCase()}
                                    </span>
                                )}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );

    return (
        <div>
            {renderSolutionTable("This is the best solution:", bestSolution)}
            {worstSolution &&
                renderSolutionTable("Other solutions:", worstSolution)}
        </div>
    );
};

export default SolutionDisplay;

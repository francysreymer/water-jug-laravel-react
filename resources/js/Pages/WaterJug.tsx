import { Head } from "@inertiajs/react";
import React, { useState } from "react";
import SolutionDisplay from "@/Components/SolutionDisplay";
import { SolutionsJson } from "@/Components/SolutionDisplay/types";
import WaterJugForm from "@/Components/WaterJugForm";
import ErrorDisplay from "@/Components/ErrorDisplay";
import CenteredText from "@/Components/CenteredText";

const WaterJug: React.FC = () => {
    const [solutions, setSolutions] = useState<SolutionsJson>({
        best_solution: [],
        worst_solution: [],
    });
    const [errors, setErrors] = useState<{ [key: string]: string[] }>({});

    const handleFormSubmit = async (x: number, y: number, z: number) => {
        try {
            const response = await fetch("/api/water-jugs", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    bucket_x: x,
                    bucket_y: y,
                    amount_wanted_z: z,
                }),
            });
            if (response.ok) {
                const data = await response.json();
                setSolutions(data.solutions);
                setErrors({}); // Clear errors on successful response
            } else {
                const errorData = await response.json();
                setErrors(errorData.errors || {});
                setSolutions({ best_solution: [], worst_solution: [] }); // Clear solutions on error
            }
        } catch (error) {
            console.error(error);
        }
    };

    return (
        <>
            <Head title="Water Jug Challenge" />
            <div>
                <CenteredText text="Water Jug Challenge" />
                <WaterJugForm onSubmit={handleFormSubmit} />
                {Object.keys(errors).length > 0 && (
                    <ErrorDisplay errors={errors} />
                )}
                {solutions.best_solution.length > 0 && (
                    <SolutionDisplay
                        bestSolution={solutions.best_solution}
                        {...(solutions.worst_solution && {
                            worstSolution: solutions.worst_solution,
                        })}
                    />
                )}
            </div>
        </>
    );
};

export default WaterJug;

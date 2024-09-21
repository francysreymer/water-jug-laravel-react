export type SolutionStep = {
    bucketX: number;
    bucketY: number;
    action: string;
    step: number;
    status?: string;
};

export type SolutionsState = {
    bestSolution: SolutionStep[];
    worstSolution?: SolutionStep[];
};

export type SolutionsJson = {
    best_solution: SolutionStep[];
    worst_solution?: SolutionStep[];
};

<?php

namespace Alkauni\Planogrid\DTO;

class PlanogramGridResult
{
    /**
     * @param array<int, array<int, CustomLabelDetection>> $matrix
     */
    public function __construct(
        public readonly array $matrix
    ) {}

    /**
     * Get geometry array formatted according to CONTEXT.md ($result_geometry)
     */
    public function getResultGeometry(): array
    {
        $resultGeometry = [];

        foreach ($this->matrix as $rowIndex => $rowItems) {
            $rowGeometry = [];
            foreach ($rowItems as $item) {
                $rowGeometry[] = $item->toArray();
            }
            $resultGeometry[] = $rowGeometry;
        }

        return $resultGeometry;
    }

    /**
     * Get brand matrix formatted according to CONTEXT.md ($result)
     */
    public function getResult(): array
    {
        $result = [];

        foreach ($this->matrix as $rowIndex => $rowItems) {
            $rowBrands = [];
            foreach ($rowItems as $colIndex => $item) {
                $brandKey = 'Brand ' . ($colIndex + 1);
                $rowBrands[$brandKey] = $item->name;
            }
            $result[] = $rowBrands;
        }

        return $result;
    }

    public function toArray(): array
    {
        return [
            'result_geometry' => $this->getResultGeometry(),
            'result' => $this->getResult(),
        ];
    }

    public function toJson(int $options = JSON_PRETTY_PRINT): string
    {
        return json_encode($this->toArray(), $options);
    }
}

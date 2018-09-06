<?php

namespace AppBundle\Statistics\Acquisition\Calculator;

use AppBundle\Statistics\Acquisition\StatisticsRequest;

abstract class AbstractCalculator implements CalculatorInterface
{
    private $calculatedData;

    final public function calculate(StatisticsRequest $request, array $keys): array
    {
        if (\is_array($this->calculatedData)) {
            return $this->calculatedData;
        }

        return $this->calculatedData = $this->fillEmptyCase($this->processing($request, $keys), $keys);
    }

    protected function fillEmptyCase(array $data, array $keys, int $value = 0): array
    {
        if (\count($data) === \count($keys)) {
            return $data;
        }

        $result = array_map('intval', array_column($data, 'total', 'date') + array_fill_keys($keys, $value));
        ksort($result);

        return $result;
    }

    protected function mergeResults(callable $callback, array $keys, ...$datas): array
    {
        return array_combine($keys, array_map($callback, ...$datas));
    }

    abstract protected function processing(StatisticsRequest $request, array $keys): array;
}

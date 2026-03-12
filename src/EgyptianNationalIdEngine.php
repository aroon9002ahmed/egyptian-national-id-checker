<?php

namespace Aroon\EgyptianNationalId;

class EgyptianNationalIdEngine
{
    /** @var EgyptianNationalId[] */
    private array $ids;

    public function __construct(array $ids)
    {
        $this->ids = [];
        foreach ($ids as $id) {
            $nid = new EgyptianNationalId($id);
            if ($nid->isValid()) {
                $this->ids[] = $nid;
            }
        }
    }

    public static function make(array $ids): self
    {
        return new self($ids);
    }

    public function get(): array
    {
        return $this->ids;
    }

    public function filter(callable $callback): array
    {
        return array_filter($this->ids, $callback);
    }

    public function stats(): array
    {
        $stats = [
            'total' => count($this->ids),
            'males' => 0,
            'females' => 0,
            'adults' => 0,
            'governorates' => [],
        ];

        foreach ($this->ids as $id) {
            if ($id->isMale()) {
                $stats['males']++;
            } else {
                $stats['females']++;
            }

            if ($id->isAdult()) {
                $stats['adults']++;
            }

            $gov = $id->getGovernorateName();
            if ($gov) {
                if (!isset($stats['governorates'][$gov])) {
                    $stats['governorates'][$gov] = 0;
                }
                $stats['governorates'][$gov]++;
            }
        }

        return $stats;
    }

    public function mapWithAnalysis(array $originalData, string $idKey = 'national_id'): array
    {
        return array_map(function ($item) use ($idKey) {
            $itemArray = (array) $item;
            if (isset($itemArray[$idKey])) {
                $nid = new EgyptianNationalId($itemArray[$idKey]);
                if ($nid->isValid()) {
                    $itemArray['analysis'] = $nid->toArray();
                } else {
                    $itemArray['analysis'] = null;
                }
            }
            
            return is_object($item) ? (object) $itemArray : $itemArray;
        }, $originalData);
    }
}

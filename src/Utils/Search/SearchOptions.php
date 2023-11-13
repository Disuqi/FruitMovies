<?php

namespace App\Utils\Search;

class SearchOptions
{
    public readonly OrderBy $orderBy;
    public readonly SortOrder $sortOrder;
    public readonly OrderBy $additionalOrderBy;
    public readonly SortOrder $additionalSortOrder;

    public readonly ?\DateTime $startDate;
    public readonly ?\DateTime $endDate;

    public readonly int $page;

    public readonly string $searchQuery;

    public function __construct(
        OrderBy $orderBy = OrderBy::None,
        SortOrder $orderSort = SortOrder::Descending,
        OrderBy $additionalOrderBy = OrderBy::None,
        SortOrder $additionalOrderSort = SortOrder::Descending,
        ?\DateTime $startDate = null,
        ?\DateTime $endDate = null,
        int $page = 0,
        string $searchQuery = ""
    )
    {
        $this->orderBy = $orderBy;
        $this->sortOrder = $orderSort;
        $this->additionalOrderBy = $additionalOrderBy;
        $this->additionalSortOrder = $additionalOrderSort;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->page = $page;
        $this->searchQuery = $searchQuery;
    }
}
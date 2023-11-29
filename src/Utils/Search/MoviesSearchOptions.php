<?php

namespace App\Utils\Search;

class MoviesSearchOptions
{
    public OrderMoviesBy $orderBy;
    public SortOrder $sortOrder;
    public OrderMoviesBy $additionalOrderBy;
    public SortOrder $additionalSortOrder;

    public ?\DateTime $startDate;
    public ?\DateTime $endDate;

    public int $page;

    public string $searchQuery;

    public function __construct(
        OrderMoviesBy $orderBy = OrderMoviesBy::None,
        SortOrder     $orderSort = SortOrder::Descending,
        OrderMoviesBy $additionalOrderBy = OrderMoviesBy::None,
        SortOrder     $additionalOrderSort = SortOrder::Descending,
        ?\DateTime    $startDate = null,
        ?\DateTime    $endDate = null,
        int           $page = 0,
        string        $searchQuery = ""
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
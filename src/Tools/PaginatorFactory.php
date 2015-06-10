<?php namespace Digbang\Doctrine\Tools;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Illuminate\Pagination\Factory;

class PaginatorFactory
{
	/**
	 * @type Factory
	 */
	private $laravelPaginationFactory;

	/**
	 * @param Factory $laravelPaginationFactory
	 */
	public function __construct(Factory $laravelPaginationFactory)
	{
		$this->laravelPaginationFactory = $laravelPaginationFactory;
	}

	/**
	 * Construct a Laravel Paginator object from a Doctrine Paginator instance.
	 *
	 * @param Paginator $paginator
	 * @return \Illuminate\Pagination\Paginator
	 */
	public function fromDoctrinePaginator(Paginator $paginator)
	{
		return $this->laravelPaginationFactory->make(
			$this->getItems($paginator),
			$paginator->count(),
			$paginator->getQuery()->getMaxResults()
		);
	}

	/**
	 * Get an array of items from a Doctrine Paginator instance.
	 *
	 * @param Paginator $paginator
	 * @return array
	 */
	private function getItems(Paginator $paginator)
	{
		$items = [];

		foreach ($paginator as $item)
		{
			$items[] = $item;
		}

		return $items;
	}
}

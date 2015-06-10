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
	public function fromDoctrinePaginator(Paginator $paginator = null)
	{
		return $this->laravelPaginationFactory->make(
			$this->getItems($paginator),
			$this->getCount($paginator),
			$this->getMaxResults($paginator)
		);
	}

	/**
	 * Get an array of items from a Doctrine Paginator instance.
	 *
	 * @param Paginator $paginator
	 * @return array
	 */
	private function getItems(Paginator $paginator = null)
	{
		$items = [];

		if ($paginator instanceof Paginator)
		{
			foreach ($paginator as $item)
			{
				$items[] = $item;
			}
		}

		return $items;
	}

	/**
	 * Get the total amount of items available.
	 *
	 * @param Paginator $paginator
	 * @return int
	 */
	private function getCount(Paginator $paginator = null)
	{
		if ($paginator === null)
		{
			return 0;
		}

		return $paginator->count();
	}

	/**
	 * Get the limit of items configured.
	 *
	 * @param Paginator $paginator
	 * @return int
	 */
	private function getMaxResults(Paginator $paginator = null)
	{
		if ($paginator === null)
		{
			// Avoid division by zero errors
			return 1;
		}

		return $paginator->getQuery()->getMaxResults();
	}
}

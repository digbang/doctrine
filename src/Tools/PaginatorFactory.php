<?php namespace Digbang\Doctrine\Tools;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

class PaginatorFactory
{
	/**
	 * Construct a Laravel Paginator object from a Doctrine Paginator instance.
	 *
	 * @param Paginator $paginator
	 * @param array     $options
	 *
	 * @return LengthAwarePaginator
	 */
	public function fromDoctrinePaginator(Paginator $paginator = null, array $options = [])
	{
		return new LengthAwarePaginator(
			$this->getItems($paginator),
			$this->getCount($paginator),
			$this->getMaxResults($paginator),
			$this->getCurrentPage($paginator),
			array_merge([
				'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()
			], $options)
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

	/**
	 * Calculates the current page from the paginator query.
	 *
	 * @param Paginator|null $paginator
	 * @return int
	 */
	private function getCurrentPage(Paginator $paginator = null)
	{
		if ($paginator === null)
		{
			return 1;
		}

		$query = $paginator->getQuery();

		$limit = $query->getMaxResults();
		$offset = $query->getFirstResult();

		if ($limit < 1)
		{
			return 1;
		}

		return 1 + ($offset / $limit);
	}
}

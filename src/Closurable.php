<?php

namespace Codrasil\Closurable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait Closurable
{
    /**
     * The table closurably associated with the model.
     *
     * @var string
     */
    protected $closureTable;

    /**
     * Retrieve the closure relation of the resource.
     * Uses the Closure Table Heirarchy Model.
     *
     * @param  string $table
     * @param  string $firstKey
     * @param  string $secondKey
     * @return \Codrasil\Closurable\Relations\ClosurablyRelatedTo
     */
    public function closurablyRelatedTo($table = null, $firstKey = null, $secondKey = null)
    {
        $table = $table ?: $this->getClosureTable();
        $firstKey = $firstKey ?: $this->getAncestorKey();
        $secondKey = $secondKey ?: $this->getDescendantKey();

        return new Relations\ClosurablyRelatedTo(
            $this->newQuery(), $this, $table, $firstKey, $secondKey
        );
    }

    /**
     * Retrieves the lineage of the closurably listed resource.
     *
     * @return \Codrasil\Closurable\Relations\ClosurablyRelatedTo
     */
    public function closurables()
    {
        return $this->closurablyRelatedTo();
    }

    /**
     * Retrieve the immediate children of the resource.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function children()
    {
        return $this->closurables()->children();
    }

    /**
     * Retrieve the immediate children of the resource.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getChildrenAttribute()
    {
        return $this->children();
    }

    /**
     * Check if resource has children.
     *
     * @return boolean
     */
    public function hasChildren()
    {
        return $this->children()->isNotEmpty();
    }

    /**
     * Retrieve the descendants attribute.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function descendants()
    {
        return $this->closurables()->descendants();
    }

    /**
     * Retrieve the descendants attribute.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDescendantsAttribute()
    {
        return $this->descendants();
    }

    /**
     * Check if resource has descendants.
     *
     * @return boolean
     */
    public function hasDescendants()
    {
        return $this->descendants()->isNotEmpty();
    }

    /**
     * Retrieve the ancestors attribute.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function ancestors()
    {
        return $this->closurables()->ancestors();
    }

    /**
     * Retrieve the ancestors attribute.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAncestorsAttribute()
    {
        return $this->ancestors();
    }

    /**
     * Check if resource has ancestors.
     *
     * @return boolean
     */
    public function hasAncestors()
    {
        return $this->ancestors()->isNotEmpty();
    }

    /**
     * Retrieve the parent attribute.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function parent()
    {
        return $this->closurables()->parent();
    }

    /**
     * Retrieve the parent attribute.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getParentAttribute()
    {
        return $this->parent();
    }

    /**
     * Check if resource has parent.
     *
     * @return boolean
     */
    public function hasParent()
    {
        return ! is_null($this->parent());
    }

    /**
     * Retrieve the siblings of the current resource.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function siblings()
    {
        if ($this->parent) {
            return $this->parent->children->find($this->getKey())->closurables()->siblings()->get();
        }

        return $this->closurables()->siblings()->get();
    }

    /**
     * Retrieve the siblings of the current resource.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getSiblingsAttribute()
    {
        return $this->siblings();
    }

    /**
     * Check if resource has siblings.
     *
     * @return boolean
     */
    public function hasSiblings()
    {
        return $this->siblings->isNotEmpty();
    }

    /**
     * Retrieve next sibling.
     * E.g.
     * ---------------------
     *    Sibling 1
     *    Sibling 2  <-- if this is the current $key value
     *    Sibling 3  <-- then we should receive this
     *    Sibling 4
     *    ...
     *
     * @return mixed
     */
    public function next()
    {
        $closurables = $this->closurables();
        $siblings = $closurables->siblings();
        $sortKey = $this->getSortKey();

        if ($siblings->exists()) {
            return $siblings->next()->first();
        }

        if ($closurables->exists()) {
            return $closurables->next()->first();
        }

        return $this->parent->children->find($this->getKey())->next();
    }

    /**
     * Check if next is not null.
     *
     * @return boolean
     */
    public function hasNext()
    {
        return ! is_null($this->next());
    }

    /**
     * Retrieve next sibling.
     *
     * @return mixed
     */
    public function getNextAttribute()
    {
        return $this->next();
    }

    /**
     * Retrieve next sibling.
     * E.g.
     * ---------------------
     *    Sibling 1
     *    Sibling 2  <-- we should receive this
     *    Sibling 3  <-- if this is the current $key value
     *    Sibling 4
     *    ...
     *
     * @return mixed
     */
    public function previous()
    {
        $closurables = $this->closurables();
        $siblings = $closurables->siblings();
        $sortKey = $this->getSortKey();

        if ($siblings->exists()) {
            return $siblings->previous()->get()->last();
        }

        if ($closurables->exists()) {
            return $closurables->previous()->get()->last();
        }

        return $this->parent->children->find($this->getKey())->previous();
    }

    /**
     * Check if previous is not null.
     *
     * @return boolean
     */
    public function hasPrevious()
    {
        return ! is_null($this->previous());
    }

    /**
     * Retrieve previous sibling.
     *
     * @return mixed
     */
    public function getPreviousAttribute()
    {
        return $this->previous();
    }

    /**
     * Get the default closure table name for the model.
     *
     * @return string
     */
    public function getClosureTable()
    {
        return $this->closureTable ?? $this->getTable().config('closurable.suffix');
    }

    /**
     * Get the default ancestor key name for the model.
     *
     * @return string
     */
    public function getAncestorKey()
    {
        return $this->ancestorKey ?? 'ancestor_id';
    }

   /**
     * Get the default descendant key name for the model.
     *
     * @return string
     */
    public function getDescendantKey()
    {
        return $this->descendantKey ?? 'descendant_id';
    }

    /**
     * Get the default root key name for the model.
     *
     * @return string
     */
    public function getRootKeyName()
    {
        return $this->rootKeyName ?? 'root';
    }

    /**
     * Get the default descendant key name for the model.
     *
     * @return string
     */
    public function getDepthKey()
    {
        return $this->depthKey ?? 'depth';
    }

    /**
     * Get the default sort key name for the model.
     *
     * @return string
     */
    public function getSortKey()
    {
        return $this->attributes[$this->sortKey ?? 'sort'] ?? $this->getKeyName();
    }

    /**
     * Retrieve only root resource nodes.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    public function scopeRoots(Builder $builder)
    {
        $closureTable = $this->getClosureTable();
        $modelKeyName = $this->getQualifiedKeyName();
        $descendantKeyName = $this->closurables()->getQualifiedDescendantKeyName();
        $ancestorKeyName = $this->closurables()->getQualifiedAncestorKeyName();
        $depthKeyName = $this->closurables()->getQualifiedDepthKeyName();
        $rootKeyName = $this->closurables()->getQualifiedRootKeyName();

        $builder
            ->join($closureTable, $descendantKeyName, '=', $modelKeyName)
            ->where($depthKeyName, 0)
            ->where($rootKeyName, '=', 1)
            ->groupBy([$descendantKeyName, $modelKeyName, $ancestorKeyName, $depthKeyName]);
    }

    /**
     * Check if model is exists on the closure table.
     *
     * @return boolean
     */
    public function isRoot()
    {
        return $this->roots()->where(
            $this->getKeyName(), $this->getKey()
        )->exists();
    }

    /**
     * Attach the model to self.
     *
     * @return self
     */
    public function attachToSelf()
    {
        $this->closurables()->attachToSelf();

        return $this;
    }
}

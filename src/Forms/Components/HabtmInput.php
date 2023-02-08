<?php

namespace AngryMoustache\HabtmField\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HabtmInput extends Field
{
    protected string $view = 'habtm-field::forms.components.habtm-input';

    public string | Closure $displayUsingView = 'habtm-field::habtm-item';

    public string | Closure $displayLabelUsing;

    public int | Closure $perPage = 10;

    public string $relationship;

    public Closure $resourceQuery;

    public function setUp(): void
    {
        parent::setUp();

        $this->relationship = $this->getName();

        $this->resourceQuery(fn (Builder $query) => $query);

        $this->displayLabelUsing(fn (Model $item) => $item->id);

        // Get the selected IDs
        $this->loadStateFromRelationshipsUsing(static function (HabtmInput $component): void {
            $relationship = $component->getRelationship();

            $state = $relationship->getResults()
                ->pluck($relationship->getRelatedKeyName())
                ->toArray();

            $component->state($state);
        });

        // Save the newly selected IDs
        $this->saveRelationshipsUsing(static function (HabtmInput $component, $state) {
            $component->getRelationship()->sync($state ?? []);
        });

        $this->dehydrated(false);
    }

    public function resourceQuery(Closure $callback): static
    {
        $this->resourceQuery = $callback;

        return $this;
    }

    public function displayUsingView(string | Closure $callback): static
    {
        $this->displayUsingView = $callback;

        return $this;
    }

    public function getViewFor(Model $item): View
    {
        $view = $this->evaluate($this->displayUsingView, [
            'item' => $item,
        ]);

        return view($view, [
            'item' => $item,
            'getLabel' => fn (): string => $this->getLabelFor($item),
        ]);
    }

    public function displayLabelUsing(string | Closure $callback): static
    {
        $this->displayLabelUsing = $callback;

        return $this;
    }

    public function getLabelFor(Model $item): string
    {
        return (string) $this->evaluate($this->displayLabelUsing, [
            'item' => $item,
        ]);
    }

    public function perPage(int | Closure $callback): static
    {
        $this->perPage = $callback;

        return $this;
    }

    public function getPerPage()
    {
        return $this->evaluate($this->perPage);
    }

    public function getResources(): Collection
    {
        $related = $this->getRelationship()->getRelated();

        return $this->resourceQuery->__invoke($related->query())->get();
    }

    public function getRelationship(): BelongsToMany
    {
        return $this->getModelInstance()->{$this->getRelationshipName()}();
    }

    public function getRelationshipName(): ?string
    {
        return $this->evaluate($this->relationship);
    }
}

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :id="$getId()"
    :label="$getLabel()"
    :label-sr-only="$isLabelHidden()"
    :helper-text="$getHelperText()"
    :hint="$getHint()"
    :hint-action="$getHintAction()"
    :hint-color="$getHintColor()"
    :hint-icon="$getHintIcon()"
    :required="$isRequired()"
    :state-path="$getStatePath()"
>
    @php
        $resources = $getResources();
        $state = collect($getState() ?? []);
        $itemsList = $resources->pluck('id')->map(function ($id) use ($state) {
            return [
                'id' => $id,
                'selected' => $state->contains($id),
            ];
        });
    @endphp

    <div x-data="{
        state: $wire.entangle('{{ $getStatePath() }}').defer,
        search: '',
        page: 1,
        perPage: @js($getPerPage()),
        items: @js($itemsList),
        select (id) {
            this.state.push(id)
            this.toggle(id)
        },
        unselect (id) {
            this.state = this.state.filter(i => i !== id)
            this.toggle(id)
        },
        toggle (id) {
            const item = this.items[this.items.findIndex(el => el.id === id)]
            item.selected = ! item.selected

            if (this.page > this.maxPage()) {
                this.page = this.maxPage()
            }
        },
        currentPage () {
            return this.items
                .filter(el => ! el.selected)
                {{-- .filter(el => el.id.toString().includes(this.search)) --}}
                .slice(
                    (this.page - 1) * this.perPage,
                    this.page * this.perPage
                )
        },
        maxPage () {
            return Math.ceil(this.items.filter(el => ! el.selected).length / this.perPage)
        },
        nextPage () {
            if (this.items.filter(el => ! el.selected).length <= this.page * this.perPage) {
                return
            }

            this.page++
        },
        prevPage () {
            if (this.page === 1) {
                return
            }

            this.page--
        },
        canBeSelected (id) {
            return ! this.state.includes(id)
                && this.currentPage().find(el => el.id === id)
        }
    }">
        <input type="text" x-bind:value="search" />

        <div class="grid grid-cols-3 gap-4">
            <div class="border rounded-xl h-48">
                @foreach ($resources as $item)
                    <div
                        x-cloak
                        x-show="canBeSelected(@js($item->id))"
                        x-on:click="select(@js($item->id))"
                        class="border-b cursor-pointer"
                        key="{{ $getStatePath() }}-option-{{ $item->id }}"
                    >
                        {{ $getViewFor($item) }}
                    </div>
                @endforeach

                {{-- TMP --}}
                <div class="flex justify-between">
                    <div class="py-2 px-4" x-on:click="prevPage()">
                        <<
                    </div>

                    <div class="py-2 px-4">
                        <span x-text="page"></span>
                        /
                        <span x-text="maxPage()"></span>
                    </div>

                    <div class="py-2 px-4" x-on:click="nextPage()">
                        >>
                    </div>
                </div>
            </div>

            <div class="border rounded-xl h-48 overflow-y-auto" style="height: 400px">
                @foreach ($getResources() as $item)
                    <div
                        x-cloak
                        x-transition
                        x-show="state.includes(@js($item->id))"
                        x-on:click="unselect(@js($item->id))"
                        class="border-b cursor-pointer"
                    >
                        {{ $getViewFor($item) }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-dynamic-component>

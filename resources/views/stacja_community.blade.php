<x-guest-layout>
    <div class="py-6">
        <div class="mx-auto sm:px-2 lg:px-8 max-w-[100rem]">
            <div class="overflow-hidden">
                @livewire('stacja-community')
            </div>
        </div>
    </div>
    @pushOnce('scripts2')
                <script>
                function stationSelect({ initialId, stations }) {
                    return {
                        open: false,
                        query: '',
                        selectedId: initialId,
                        stations,
                        filtered() {
                            const q = this.query.toLowerCase();
                            return Object.entries(this.stations).filter(([id, name]) =>
                                id.includes(q) || name.toLowerCase().includes(q)
                            );
                        },
                        select(id) {
                            this.selectedId = id;
                            this.query = this.stations[id] ? `${this.stations[id]}` : id;
                            this.open = false;
                             this.$wire.set('stationId', id).then(() => {
                                if (this.stations[id]) {
                                   this.$wire.call('loadData');

                                } else {
                                    this.$wire.set('weatherData', null); // Clear data if ID is invalid

                                }
                            });
                            this.$wire.set('stop', false);

                            console.log(id);
                        },
                        init() {
                            // Populate input with name if stationId is passed in URL
                            if (this.selectedId && this.stations[this.selectedId]) {
                                this.query = this.stations[this.selectedId];
                            }else{
                                this.$wire.set('stop', true);
                            }

                        }
                    };
                }
                </script>
    @endpushOnce
</x-guest-layout>

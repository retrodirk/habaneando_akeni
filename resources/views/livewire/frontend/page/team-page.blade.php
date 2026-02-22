<!-- Include Alpine.js (if not already included) -->
<script src="//unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<div x-data="teamModal()" class="relative">
    <x-hero :title="$page->name" :header="$page->header" />

    <x-container>
        <div class="mx-auto max-w-7xl px-6 text-center lg:px-8">
            <p>{!! $page->body !!}</p>
            <ul role="list"
                class="mx-auto mt-20 grid max-w-2xl grid-cols-1 gap-x-8 gap-y-16 sm:grid-cols-2 lg:mx-0 lg:max-w-none lg:grid-cols-3">
                @foreach ($members->where('is_staff', true) as $member)
                    <li class="cursor-pointer"
                        @click="openModal({{ json_encode([
                            'id' => $member->id,
                            'full_name' => $member->full_name,
                            'thumbnail' => env('DO_CDN') . '/' . $member->thumbnail,
                            'styles' => $member->styles,
                            'instagram' => $member->instagram ?? null,
                            'facebook' => $member->facebook ?? null,
                            'bio' => $member->description,
                        ]) }})">
                        <img class="aspect-[3/2] w-full rounded-2xl object-cover"
                            src="{{ env('DO_CDN') . '/' . $member->thumbnail }}" alt="{{ $member->full_name }}">
                        <h3 class="mt-6 text-base font-semibold leading-7 tracking-tight text-neutral-900">
                            {{ $member->full_name }}
                        </h3>
                        <p class="text-sm leading-6 text-neutral-600">{{ $member->styles }}</p>
                        <a href="#" 
                           @click.prevent="openModal({{ json_encode([
                               'id' => $member->id,
                               'full_name' => $member->full_name,
                               'thumbnail' => env('DO_CDN') . '/' . $member->thumbnail,
                               'styles' => $member->styles,
                               'instagram' => $member->instagram ?? null,
                               'facebook' => $member->facebook ?? null,
                               'bio' => $member->description,
                           ]) }})"
                           class="mt-3 inline-block text-sm font-medium text-black-600 hover:text-blue-800 transition-colors">
                            Mehr →
                        </a>
                        <ul role="list" class="mt-6 flex justify-center gap-x-6">
                            @if ($member->instagram)
                                <li>
                                    <a href="{{ $member->instagram }}" target="_blank"
                                        class="text-neutral-400 hover:text-neutral-500">
                                        <span class="sr-only">Instagram</span>
                                        <x-icons.instagram />
                                    </a>
                                </li>
                            @endif
                            @if ($member->facebook)
                                <li>
                                    <a href="{{ $member->facebook }}" target="_blank"
                                        class="text-neutral-400 hover:text-neutral-500">
                                        <span class="sr-only">Facebook</span>
                                        <x-icons.facebook />
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endforeach
                <!-- More people... -->
            </ul>
        </div>
    </x-container>

    <!-- Modal -->
    <div x-show="showModal" x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 md:translate-y-0 md:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 md:scale-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 md:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 md:translate-y-0 md:scale-95"
        class="fixed inset-0 z-10 overflow-y-auto" aria-modal="true" role="dialog">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div
                class="relative w-full max-w-3xl transform overflow-hidden rounded-2xl bg-white px-4 pt-5 pb-4 text-left shadow-xl transition-all">
                <button type="button" @click="closeModal"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-500">
                    <span class="sr-only">Close</span>
                    <!-- Heroicon: x-mark -->
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="sm:flex sm:items-start">
                    <div
                        class="mx-auto flex-shrink-0 flex items-center justify-center h-24 w-24 rounded-full overflow-hidden sm:mx-0 sm:h-24 sm:w-24">
                        <img :src="currentMember.thumbnail" :alt="currentMember.full_name">
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg font-medium leading-6 text-gray-900" x-text="currentMember.full_name"></h3>
                        <p class="text-sm text-gray-500" x-text="currentMember.styles"></p>
                        <div class="mt-4 text-sm text-gray-700" x-html="currentMember.bio"></div>
                        <div class="mt-4 flex space-x-4">
                            <template x-if="currentMember.instagram">
                                <a :href="currentMember.instagram" target="_blank"
                                    class="text-neutral-400 hover:text-neutral-500">
                                    <span class="sr-only">Instagram</span>
                                    <x-icons.instagram />
                                </a>
                            </template>
                            <template x-if="currentMember.facebook">
                                <a :href="currentMember.facebook" target="_blank"
                                    class="text-neutral-400 hover:text-neutral-500">
                                    <span class="sr-only">Facebook</span>
                                    <x-icons.facebook />
                                </a>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Additional modal content can be added here -->

            </div>
        </div>
    </div>
</div>

<!-- AlpineJS component for modal -->
<script>
    function teamModal() {
        return {
            showModal: false,
            currentMember: {},
            openModal(member) {
                this.currentMember = member;
                this.showModal = true;
            },
            closeModal() {
                this.showModal = false;
            }
        }
    }
</script>

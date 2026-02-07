<div
     @if (filament()->isSidebarCollapsibleOnDesktop())
        x-bind:class="$store.sidebar.isOpen ? 'block' : 'hidden'"
    @endif
>
    <x-filament::input.wrapper
        class="relative"
        :inline-prefix="true"
    >
        <x-filament::input
            type="text"
            placeholder="{{ __('Search...') }}"
            x-data="sidebarSearch()"
            x-ref="search"
            x-on:input.debounce.300ms="filterItems($event.target.value)"
            x-on:keydown.escape="clearSearch"
            x-on:keydown.meta.j.prevent.document="$refs.search.focus()"
        />


    </x-filament::input.wrapper>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('sidebarSearch', () => ({
                init() {
                    this.$refs.search.value = ''
                },

                filterItems(searchTerm) {
                    const groups = document.querySelectorAll('.fi-sidebar-nav-groups .fi-sidebar-group')
                    searchTerm = searchTerm.toLowerCase()

                    groups.forEach(group => {
                        const groupButton = group.querySelector('.fi-sidebar-group-button')
                        const groupText = groupButton?.textContent.toLowerCase() || ''
                        const items = group.querySelectorAll('.fi-sidebar-item')
                        let hasVisibleItems = false

                        const groupMatches = groupText.includes(searchTerm)

                        items.forEach(item => {
                            const itemText = item.textContent.toLowerCase()
                            const isVisible = itemText.includes(searchTerm) || groupMatches

                            item.style.display = isVisible ? '' : 'none'
                            if (isVisible) hasVisibleItems = true
                        })

                        group.style.display = (hasVisibleItems || groupMatches) ? '' : 'none'
                    })
                },

                clearSearch() {
                    this.$refs.search.value = ''
                    this.filterItems('')
                }
            }))
        })
    </script>
</div>

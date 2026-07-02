@props([
    'label' => null,
    'id' => null,
])

@php
    $pickerId = filled($id) ? $id : 'date-picker-'.\Illuminate\Support\Str::random(8);
    $wireAttributes = $attributes->whereStartsWith('wire:');
    $extraClass = trim((string) ($attributes->get('class') ?? ''));
    $triggerClass = trim('form-input tyro-date-trigger'.($extraClass !== '' ? ' '.$extraClass : ''));
    $hasLabel = filled($label);
@endphp

<div
    class="tyro-date-picker form-group"
    style="margin-bottom: 0;"
    x-data="tyroDatePicker"
    x-on:keydown.escape.window="close()"
    x-on:click.outside="close()"
>
    @if ($hasLabel)
        <label class="form-label" for="{{ $pickerId }}">{{ $label }}</label>
    @endif

    <input type="hidden" x-ref="model" {!! $wireAttributes !!}>

    <div class="tyro-date-control">
        <button
            type="button"
            id="{{ $pickerId }}"
            class="{{ $triggerClass }}"
            x-on:click="toggle()"
            :aria-expanded="open"
            aria-haspopup="dialog"
        >
            <span :class="{ 'tyro-date-placeholder': !selected }" x-text="selected ? displayValue : 'Select date'"></span>
        </button>
        <span class="tyro-date-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </span>
    </div>

    <div class="tyro-date-menu" x-show="open" x-cloak x-transition>
        <div class="tyro-date-menu-header">
            <button type="button" class="tyro-date-nav" x-on:click="prevMonth()" aria-label="Previous month">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
            </button>
            <div class="tyro-date-month" x-text="monthLabel"></div>
            <button type="button" class="tyro-date-nav" x-on:click="nextMonth()" aria-label="Next month">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
            </button>
        </div>

        <div class="tyro-date-weekdays">
            <template x-for="weekday in weekdays" :key="weekday">
                <span x-text="weekday"></span>
            </template>
        </div>

        <div class="tyro-date-grid">
            <template x-for="(day, index) in calendarDays" :key="index">
                <button
                    type="button"
                    class="tyro-date-day"
                    :class="{
                        'is-outside': !day.inMonth,
                        'is-today': day.isToday,
                        'is-selected': day.iso === selected,
                    }"
                    :disabled="!day.inMonth"
                    x-on:click="selectDate(day.iso)"
                    x-text="day.label"
                ></button>
            </template>
        </div>
    </div>
</div>

@once
    @push('styles')
        <style>
            [x-cloak] {
                display: none !important;
            }

            .tyro-date-picker {
                position: relative;
                display: block;
                width: 100%;
            }

            .tyro-date-control {
                position: relative;
            }

            .tyro-date-trigger {
                display: flex;
                align-items: center;
                width: 100%;
                text-align: left;
                cursor: pointer;
                padding-right: 2.75rem;
            }

            .tyro-date-placeholder {
                color: var(--muted-foreground);
            }

            .tyro-date-icon {
                position: absolute;
                top: 50%;
                right: 0.875rem;
                transform: translateY(-50%);
                display: inline-flex;
                color: var(--muted-foreground);
                pointer-events: none;
            }

            .tyro-date-icon svg {
                width: 1rem;
                height: 1rem;
            }

            .tyro-date-menu {
                position: absolute;
                top: calc(100% + 0.375rem);
                left: 0;
                z-index: 50;
                width: min(100%, 18rem);
                padding: 0.75rem;
                border: 1px solid var(--border);
                border-radius: 10px;
                background: var(--popover, var(--background));
                color: var(--popover-foreground, var(--foreground));
                box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
            }

            .tyro-date-menu-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.5rem;
                margin-bottom: 0.75rem;
            }

            .tyro-date-month {
                font-size: 0.9375rem;
                font-weight: 600;
                text-align: center;
                flex: 1;
            }

            .tyro-date-nav {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 2rem;
                height: 2rem;
                border: 1px solid var(--border);
                border-radius: 8px;
                background: var(--background);
                color: var(--foreground);
                cursor: pointer;
            }

            .tyro-date-nav:hover {
                background: var(--muted);
            }

            .tyro-date-nav svg {
                width: 1rem;
                height: 1rem;
            }

            .tyro-date-weekdays,
            .tyro-date-grid {
                display: grid;
                grid-template-columns: repeat(7, minmax(0, 1fr));
                gap: 0.25rem;
            }

            .tyro-date-weekdays {
                margin-bottom: 0.375rem;
            }

            .tyro-date-weekdays span {
                font-size: 0.75rem;
                font-weight: 600;
                color: var(--muted-foreground);
                text-align: center;
            }

            .tyro-date-day {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 100%;
                aspect-ratio: 1;
                border: 0;
                border-radius: 8px;
                background: transparent;
                color: var(--foreground);
                font-size: 0.875rem;
                cursor: pointer;
            }

            .tyro-date-day:hover:not(:disabled) {
                background: var(--muted);
            }

            .tyro-date-day.is-outside {
                visibility: hidden;
            }

            .tyro-date-day.is-today {
                border: 1px solid var(--border);
            }

            .tyro-date-day.is-selected {
                background: var(--primary);
                color: var(--primary-foreground);
            }

            .tyro-date-day.is-selected.is-today {
                border-color: transparent;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function registerTyroDatePicker() {
                const register = () => Alpine.data('tyroDatePicker', () => ({
                    open: false,
                    selected: '',
                    viewYear: new Date().getFullYear(),
                    viewMonth: new Date().getMonth(),
                    weekdays: ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'],

                    init() {
                        this.syncFromModel();
                        this.$refs.model.addEventListener('input', () => this.syncFromModel());
                    },

                    syncFromModel() {
                        this.selected = this.$refs.model.value || '';
                        if (!this.selected) {
                            return;
                        }

                        const date = this.parseIso(this.selected);
                        if (!date) {
                            return;
                        }

                        this.viewYear = date.getFullYear();
                        this.viewMonth = date.getMonth();
                    },

                    get displayValue() {
                        const date = this.parseIso(this.selected);
                        if (!date) {
                            return '';
                        }

                        return date.toLocaleDateString(undefined, {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric',
                        });
                    },

                    get monthLabel() {
                        return new Date(this.viewYear, this.viewMonth, 1).toLocaleDateString(undefined, {
                            month: 'long',
                            year: 'numeric',
                        });
                    },

                    get calendarDays() {
                        const firstDay = new Date(this.viewYear, this.viewMonth, 1);
                        const daysInMonth = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
                        const startOffset = (firstDay.getDay() + 6) % 7;
                        const todayIso = this.toIso(new Date());
                        const days = [];

                        for (let index = 0; index < startOffset; index += 1) {
                            days.push({
                                label: '',
                                iso: '',
                                inMonth: false,
                                isToday: false,
                            });
                        }

                        for (let day = 1; day <= daysInMonth; day += 1) {
                            const date = new Date(this.viewYear, this.viewMonth, day);
                            const iso = this.toIso(date);

                            days.push({
                                label: String(day),
                                iso,
                                inMonth: true,
                                isToday: iso === todayIso,
                            });
                        }

                        while (days.length % 7 !== 0) {
                            days.push({
                                label: '',
                                iso: '',
                                inMonth: false,
                                isToday: false,
                            });
                        }

                        return days;
                    },

                    toggle() {
                        this.open = !this.open;
                    },

                    close() {
                        this.open = false;
                    },

                    prevMonth() {
                        if (this.viewMonth === 0) {
                            this.viewMonth = 11;
                            this.viewYear -= 1;
                            return;
                        }

                        this.viewMonth -= 1;
                    },

                    nextMonth() {
                        if (this.viewMonth === 11) {
                            this.viewMonth = 0;
                            this.viewYear += 1;
                            return;
                        }

                        this.viewMonth += 1;
                    },

                    selectDate(iso) {
                        if (!iso) {
                            return;
                        }

                        this.selected = iso;
                        this.$refs.model.value = iso;
                        this.$refs.model.dispatchEvent(new Event('input', { bubbles: true }));
                        this.close();
                    },

                    parseIso(value) {
                        if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) {
                            return null;
                        }

                        const date = new Date(value + 'T00:00:00');
                        return Number.isNaN(date.getTime()) ? null : date;
                    },

                    toIso(date) {
                        const year = date.getFullYear();
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const day = String(date.getDate()).padStart(2, '0');

                        return `${year}-${month}-${day}`;
                    },
                }));

                if (window.Alpine) {
                    register();
                } else {
                    document.addEventListener('alpine:init', register);
                }
            })();
        </script>
    @endpush
@endonce

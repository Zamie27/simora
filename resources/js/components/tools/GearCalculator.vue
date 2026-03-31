<script setup lang="ts">
import { Calculator, Gauge, Milestone, ArrowRightLeft } from 'lucide-vue-next';
import { ref, computed } from 'vue';

// Standard Road Bike tire circumference (meters)
const tireSizes = [
    { name: '700 x 23c', circumference: 2.097 },
    { name: '700 x 25c', circumference: 2.105 },
    { name: '700 x 28c', circumference: 2.136 },
    { name: '700 x 32c', circumference: 2.155 },
];

const frontChainring = ref(52);
const rearCassette = ref(15);
const selectedTire = ref(tireSizes[1]);
const cadence = ref(90);

const gearRatio = computed(() =>
    (frontChainring.value / rearCassette.value).toFixed(2),
);
const metersDevelopment = computed(() =>
    (Number(gearRatio.value) * selectedTire.value.circumference).toFixed(2),
);
const speedKph = computed(() =>
    ((Number(metersDevelopment.value) * cadence.value * 60) / 1000).toFixed(1),
);

// Common roadbike gear options
const chainrings = [34, 36, 39, 42, 44, 46, 48, 50, 52, 53, 54, 55];
const cogs = [
    10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 21, 23, 24, 25, 27, 28, 30, 32, 34,
];
</script>

<template>
    <div
        class="flex flex-col gap-8 rounded-[2.5rem] border border-border bg-card p-8 shadow-2xl shadow-black/10"
    >
        <div class="flex items-center gap-4">
            <div
                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-accent/10 text-accent"
            >
                <Calculator class="h-6 w-6" />
            </div>
            <div>
                <h2
                    class="text-xl font-black tracking-tight text-foreground uppercase"
                >
                    Gear Calculator
                </h2>
                <p
                    class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60"
                >
                    Optimize your drivetrain efficiency
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4">
            <!-- Front Chainring -->
            <div class="flex flex-col gap-3">
                <label
                    class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                    >Front Chainring (T)</label
                >
                <select
                    v-model="frontChainring"
                    class="h-14 w-full appearance-none rounded-2xl border-none bg-muted/30 px-6 text-lg font-black focus:ring-2 focus:ring-accent"
                >
                    <option v-for="t in chainrings" :key="t" :value="t">
                        {{ t }}T
                    </option>
                </select>
            </div>

            <!-- Rear Cassette -->
            <div class="flex flex-col gap-3">
                <label
                    class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                    >Rear Cog (T)</label
                >
                <select
                    v-model="rearCassette"
                    class="h-14 w-full appearance-none rounded-2xl border-none bg-muted/30 px-6 text-lg font-black focus:ring-2 focus:ring-accent"
                >
                    <option v-for="t in cogs" :key="t" :value="t">
                        {{ t }}T
                    </option>
                </select>
            </div>

            <!-- Tire Size -->
            <div class="flex flex-col gap-3">
                <label
                    class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                    >Tire Size</label
                >
                <select
                    v-model="selectedTire"
                    class="h-14 w-full appearance-none rounded-2xl border-none bg-muted/30 px-6 text-lg font-black focus:ring-2 focus:ring-accent"
                >
                    <option
                        v-for="size in tireSizes"
                        :key="size.name"
                        :value="size"
                    >
                        {{ size.name }}
                    </option>
                </select>
            </div>

            <!-- Cadence -->
            <div class="flex flex-col gap-3">
                <label
                    class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                    >Cadence (RPM)</label
                >
                <div class="relative">
                    <input
                        type="number"
                        v-model="cadence"
                        class="h-14 w-full rounded-2xl border-none bg-muted/30 px-6 text-lg font-black focus:ring-2 focus:ring-accent"
                    />
                    <span
                        class="absolute top-1/2 right-6 -translate-y-1/2 text-[10px] font-black text-muted-foreground uppercase"
                        >RPM</span
                    >
                </div>
            </div>
        </div>

        <!-- Results -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div
                class="flex flex-col items-center justify-center rounded-3xl border border-border/50 bg-secondary/30 p-8"
            >
                <ArrowRightLeft class="mb-4 h-6 w-6 text-accent opacity-50" />
                <span
                    class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                    >Gear Ratio</span
                >
                <span class="mt-2 text-4xl font-black text-foreground">{{
                    gearRatio
                }}</span>
            </div>

            <div
                class="flex flex-col items-center justify-center rounded-3xl border border-border/50 bg-secondary/30 p-8"
            >
                <Milestone class="mb-4 h-6 w-6 text-accent opacity-50" />
                <span
                    class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                    >Meters Development</span
                >
                <div class="mt-2 flex items-baseline gap-1">
                    <span class="text-4xl font-black text-foreground">{{
                        metersDevelopment
                    }}</span>
                    <span
                        class="text-[10px] font-black text-muted-foreground uppercase"
                        >m</span
                    >
                </div>
            </div>

            <div
                class="flex flex-col items-center justify-center rounded-3xl bg-accent p-8 shadow-xl shadow-accent/20"
            >
                <Gauge class="mb-4 h-6 w-6 text-white opacity-50" />
                <span
                    class="text-[10px] font-black tracking-widest text-white/60 uppercase"
                    >Estimated Speed</span
                >
                <div class="mt-2 flex items-baseline gap-2">
                    <span
                        class="text-5xl font-black tracking-tighter text-white italic"
                        >{{ speedKph }}</span
                    >
                    <span class="text-xs font-black text-white uppercase italic"
                        >KM/H</span
                    >
                </div>
            </div>
        </div>
    </div>
</template>

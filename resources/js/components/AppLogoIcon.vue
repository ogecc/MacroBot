<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        :class="className"
        v-bind="$attrs"
    >
        <!-- MacroBot robot head — fill-rule evenodd: eyes + mouth become transparent holes -->
        <path
            fill="currentColor"
            fill-rule="evenodd"
            clip-rule="evenodd"
            d="M10.5 2 A1.5 1.5 0 1 1 13.5 2 A1.5 1.5 0 1 1 10.5 2 Z M11 3.5 H13 V5.5 H11 Z M6 5.5 H18 Q22 5.5 22 9.5 V18 Q22 22 18 22 H6 Q2 22 2 18 V9.5 Q2 5.5 6 5.5 Z M6 13 A2.5 2.5 0 1 1 11 13 A2.5 2.5 0 1 1 6 13 Z M13 13 A2.5 2.5 0 1 1 18 13 A2.5 2.5 0 1 1 13 13 Z M8 18 H16 Q17 18 17 19 Q17 20 16 20 H8 Q7 20 7 19 Q7 18 8 18 Z"
        />
    </svg>
</template>

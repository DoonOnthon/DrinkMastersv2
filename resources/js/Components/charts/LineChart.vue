<template>
  <v-chart :option="chartOptions" class="w-full h-64" autoresize />
</template>

<script setup>
import { computed } from 'vue'
import { use } from 'echarts/core'
import {
  CanvasRenderer
} from 'echarts/renderers'
import {
  LineChart
} from 'echarts/charts'
import {
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent
} from 'echarts/components'
import VChart from 'vue-echarts'

// Register ECharts components
use([
  CanvasRenderer,
  LineChart,
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent
])

const props = defineProps({
  data: {
    type: Array,
    required: true
  }
})

const chartOptions = computed(() => ({
  title: {
    text: 'XP Growth (Last 7 Days)'
  },
  tooltip: {
    trigger: 'axis'
  },
  legend: {
    data: ['XP']
  },
  xAxis: {
    type: 'category',
    data: (props.data ?? []).map(item => item.label)
  },
  yAxis: {
    type: 'value'
  },
  series: [
    {
      name: 'XP',
      type: 'line',
      data: (props.data ?? []).map(item => item.value),
      smooth: true
    }
  ]
}))

</script>

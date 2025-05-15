<template>
    <div class="container">
        <div class="sidebar">
            <h1>Crypto Candlestick Charts</h1>
            <select v-model="selectedSymbol">
                <option v-for="crypto in cryptos" :key="crypto.symbol" :value="crypto.symbol">
                    {{ crypto.name }} ({{ crypto.symbol }})
                </option>
            </select>
            <select v-model="selectedInterval">
                <option value="1m">1 minute</option>
                <option value="5m">5 minutes</option>
                <option value="30m">30 minutes</option>
                <option value="1h">1 hour</option>
                <option value="4h">4 hours</option>
                <option value="1d">1 day</option>
                <option value="1w">1 week</option>
                <option value="1M">1 month</option>
            </select>
            <div v-if="!candles.length">
                <p>Select a cryptocurrency to view its chart.</p>
            </div>
        </div>
        <div class="chart-area">
            <div v-if="candles.length" style="width: 100%; height: 400px;">
                <apexchart type="candlestick" height="400" :options="chartOptions" :series="series" />
            </div>
        </div>
    </div>
</template>

<script>
import VueApexCharts from 'vue3-apexcharts'

export default {
    name: 'HelloWorld',
    components: { apexchart: VueApexCharts },
    data() {
        return {
            cryptos: [
                { name: 'Bitcoin', symbol: 'BTCUSDT' },
                { name: 'Ethereum', symbol: 'ETHUSDT' },
                { name: 'Solana', symbol: 'SOLUSDT' },
                { name: 'Dogecoin', symbol: 'DOGEUSDT' },
                { name: 'Cardano', symbol: 'ADAUSDT' },
                { name: 'XRP', symbol: 'XRPUSDT' },
                { name: 'Litecoin', symbol: 'LTCUSDT' },
                { name: 'Polkadot', symbol: 'DOTUSDT' },
                { name: 'Avalanche', symbol: 'AVAXUSDT' },
                { name: 'Shiba Inu', symbol: 'SHIBUSDT' }
            ],
            selectedSymbol: 'BTCUSDT',
            selectedInterval: '1h',
            candles: [],
        }
    },
    computed: {
        chartOptions() {
            return {
                chart: {
                    type: 'candlestick',
                    height: 400,
                    width: '100%',
                    animations: {
                        enabled: true
                    }
                },
                title: {
                    text: this.selectedSymbol,
                    align: 'left'
                },
                xaxis: {
                    type: 'datetime'
                },
                yaxis: {
                    tooltip: {
                        enabled: true
                    }
                }
            }
        },
        series() {
            return [
                {
                    name: this.selectedSymbol,
                    data: this.candles.map(candle => ({
                        x: candle.time,
                        y: [candle.open, candle.high, candle.low, candle.close]
                    }))
                }
            ]
        }
    },
    methods: {
        async fetchCandles() {
            if (!this.selectedSymbol) return;
            const url = `https://api.binance.com/api/v3/klines?symbol=${this.selectedSymbol}&interval=${this.selectedInterval}&limit=30`;
            try {
                const res = await fetch(url);
                const klines = await res.json();
                this.candles = klines.map(k => ({
                    time: k[0],
                    open: parseFloat(k[1]),
                    high: parseFloat(k[2]),
                    low: parseFloat(k[3]),
                    close: parseFloat(k[4]),
                    volume: parseFloat(k[5])
                }));
            } catch (e) {
                this.candles = [];
            }
        }
    },
    mounted() {
        this.fetchCandles();
    },
    watch: {
        selectedSymbol() {
            this.fetchCandles();
        },
        selectedInterval() {
            this.fetchCandles();
        }
    }
}
</script>

<style scoped>
.container {
    display: flex;
    flex-direction: row;
    align-items: flex-start;
    justify-content: flex-start;
    min-height: 100vh;
}
.sidebar {
    min-width: 250px;
    max-width: 300px;
    padding: 2em 1em 1em 2em;
    background: #f8f9fa;
    border-right: 1px solid #e0e0e0;
    height: 100vh;
}
.chart-area {
    flex: 1;
    display: flex;
    align-items: flex-start;
    justify-content: flex-end;
    padding: 2em;
}
select {
    margin-bottom: 1em;
    width: 100%;
    padding: 0.5em;
    font-size: 1em;
}
h1 {
    font-size: 1.3em;
    margin-bottom: 1em;
}
</style>
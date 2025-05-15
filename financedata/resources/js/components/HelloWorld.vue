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
            <div>
                <div><label><input type="checkbox" v-model="indicators.rsi"> RSI</label></div>
                <div><label><input type="checkbox" v-model="indicators.macd"> MACD</label></div>
                <div><label><input type="checkbox" v-model="indicators.bollinger"> Bollinger Bands</label></div>
                <div><label><input type="checkbox" v-model="indicators.sma20"> SMA20</label></div>
                <div><label><input type="checkbox" v-model="indicators.sma50"> SMA50</label></div>
                <div><label><input type="checkbox" v-model="indicators.sma200"> SMA200</label></div>
            </div>
            <div v-if="!candles.length">
                <p>Select a cryptocurrency to view its chart.</p>
            </div>
        </div>
        <div class="chart-area" style="display: flex; flex-direction: column;">
            <div v-if="candles.length" style="width: 100%; height: 400px;">
                <apexchart type="candlestick" height="400" :options="chartOptions" :series="series" />
            </div>
            <div v-if="indicators.rsi" style="width: 100%; height: 120px;">
                <apexchart type="line" height="120" :options="rsiOptions" :series="rsiSeries" />
            </div>
            <div v-if="indicators.macd" style="width: 100%; height: 120px;">
                <apexchart type="line" height="120" :options="macdOptions" :series="macdSeries" />
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
            indicators: {
                rsi: false,
                macd: false,
                bollinger: false,
                sma20: false,
                sma50: false,
                sma200: false
            },
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
        rsiOptions() {
            return {
                chart: {
                    type: 'line',
                    height: 120,
                    width: '100%',
                    animations: {
                        enabled: true
                    }
                },
                title: {
                    text: 'RSI',
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
        macdOptions() {
            return {
                chart: {
                    type: 'line',
                    height: 120,
                    width: '100%',
                    animations: {
                        enabled: true
                    }
                },
                title: {
                    text: 'MACD',
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
            const series = [
                {
                    name: this.selectedSymbol,
                    data: this.candles.map(candle => ({
                        x: candle.time,
                        y: [candle.open, candle.high, candle.low, candle.close]
                    }))
                }
            ];

            if (this.indicators.rsi) {
                series.push({
                    name: 'RSI',
                    type: 'line',
                    data: this.candles.map(candle => ({
                        x: candle.time,
                        y: this.calculateRSI(this.candles.slice(0, this.candles.indexOf(candle) + 1))
                    }))
                });
            }

            if (this.indicators.macd) {
                series.push({
                    name: 'MACD',
                    type: 'line',
                    data: this.candles.map(candle => ({
                        x: candle.time,
                        y: this.calculateMACD(candle)
                    }))
                });
            }

            if (this.indicators.bollinger) {
                series.push({
                    name: 'Bollinger Bands',
                    type: 'line',
                    data: this.candles.map(candle => ({
                        x: candle.time,
                        y: this.calculateBollingerBands(candle)
                    }))
                });
            }

            if (this.indicators.sma20) {
                series.push({
                    name: 'SMA20',
                    type: 'line',
                    data: this.candles.map((candle, index) => ({
                        x: candle.time,
                        y: this.calculateSMA(this.candles.slice(0, index + 1), 20)
                    }))
                });
            }

            if (this.indicators.sma50) {
                series.push({
                    name: 'SMA50',
                    type: 'line',
                    data: this.candles.map((candle, index) => ({
                        x: candle.time,
                        y: this.calculateSMA(this.candles.slice(0, index + 1), 50)
                    }))
                });
            }

            if (this.indicators.sma200) {
                series.push({
                    name: 'SMA200',
                    type: 'line',
                    data: this.candles.map((candle, index) => ({
                        x: candle.time,
                        y: this.calculateSMA(this.candles.slice(0, index + 1), 200)
                    }))
                });
            }

            return series;
        },
        rsiSeries() {
            return [
                {
                    name: 'RSI',
                    data: this.candles.map(candle => ({
                        x: candle.time,
                        y: this.calculateRSI(this.candles.slice(0, this.candles.indexOf(candle) + 1))
                    }))
                }
            ];
        },
        macdSeries() {
            return [
                {
                    name: 'MACD',
                    data: this.candles.map(candle => ({
                        x: candle.time,
                        y: this.calculateMACD(candle)
                    }))
                }
            ];
        }
    },
    methods: {
        calculateRSI(candles) {
            const period = 14;
            if (candles.length < period) return null;

            const changes = candles.map((candle, index) => {
                if (index === 0) return 0;
                return candle.close - candles[index - 1].close;
            });

            const gains = changes.map(change => change > 0 ? change : 0);
            const losses = changes.map(change => change < 0 ? -change : 0);

            let avgGain = gains.slice(0, period).reduce((sum, gain) => sum + gain, 0) / period;
            let avgLoss = losses.slice(0, period).reduce((sum, loss) => sum + loss, 0) / period;

            for (let i = period; i < candles.length; i++) {
                avgGain = (avgGain * (period - 1) + gains[i]) / period;
                avgLoss = (avgLoss * (period - 1) + losses[i]) / period;
            }

            const rs = avgGain / avgLoss;
            return 100 - (100 / (1 + rs));
        },
        calculateMACD(candle) {
            // Placeholder for MACD calculation
            return 0;
        },
        calculateBollingerBands(candle) {
            // Placeholder for Bollinger Bands calculation
            return [candle.close, candle.close, candle.close];
        },
        calculateSMA(candles, period) {
            if (candles.length < period) return null;
            const lastNCandles = candles.slice(-period);
            const sum = lastNCandles.reduce((acc, candle) => acc + candle.close, 0);
            return sum / period;
        },
        async fetchCandles() {
            if (!this.selectedSymbol) return;
            const url = `https://api.binance.com/api/v3/klines?symbol=${this.selectedSymbol}&interval=${this.selectedInterval}&limit=300`;
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
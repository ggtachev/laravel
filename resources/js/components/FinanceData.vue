<template>
    <div class="container">
        <div class="sidebar">
            <h1>GT Trading vue</h1>
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
        </div>
        <div class="chart-area" style="display: flex; flex-direction: column;">
            <div v-if="candles.length" style="width: 100%; height: 400px;">
                <apexchart type="candlestick" height="400" :options="chartOptions" :series="series" />
            </div>
            <div v-if="indicators.rsi" style="width: 100%; height: 120px;">
                <apexchart type="line" height="120" :options="rsiOptions" :series="rsiSeries" />
            </div>
            <div v-if="indicators.macd" style="width: 100%; height: 240px;">
                <apexchart type="line" height="240" :options="macdOptions" :series="macdSeries" />
            </div>
        </div>
    </div>
</template>

<script>
import VueApexCharts from 'vue3-apexcharts'

export default {
    name: 'FinanceData',
    components: { apexchart: VueApexCharts },
    data() {
        return {
            cryptos: [],
            selectedSymbol: 'BTCUSDT',
            selectedInterval: '1d',
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
                    },
                    labels: {
                        formatter: (value) => value.toFixed(2)
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
                    },
                    labels: {
                        formatter: (value) => value.toFixed(2)
                    }
                },
                stroke: {
                    width: 1.5
                }
            }
        },
        macdOptions() {
            return {
                chart: {
                    type: 'line',
                    height: 240,
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
                    },
                    labels: {
                        formatter: (value) => value.toFixed(2)
                    }
                },
                stroke: {
                    width: 1.5
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

            /* Don't show macd and rsi on the main chart
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
            */

            if (this.indicators.bollinger) {
                const bollingerData = this.candles.map(candle => {
                    const bands = this.calculateBollingerBands(candle);
                    return {
                        x: candle.time,
                        y: bands
                    };
                });

                series.push({
                    name: 'Bollinger Bands',
                    type: 'line',
                    data: bollingerData.map(d => ({
                        x: d.x,
                        y: d.y ? d.y[0] : null // Upper Band
                    }))
                });
                series.push({
                    name: 'Middle Band',
                    type: 'line',
                    data: bollingerData.map(d => ({
                        x: d.x,
                        y: d.y ? d.y[1] : null // Middle Band (SMA)
                    }))
                });
                series.push({
                    name: 'Lower Band',
                    type: 'line',
                    data: bollingerData.map(d => ({
                        x: d.x,
                        y: d.y ? d.y[2] : null // Lower Band
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
                    name: 'MACD Line',
                    type: 'line',
                    data: this.candles.map(candle => {
                        const macd = this.calculateMACD(candle);
                        return {
                            x: candle.time,
                            y: macd ? macd[0] : null
                        };
                    })
                },
                {
                    name: 'Signal Line',
                    type: 'line',
                    data: this.candles.map(candle => {
                        const macd = this.calculateMACD(candle);
                        return {
                            x: candle.time,
                            y: macd ? macd[1] : null
                        };
                    })
                },
                {
                    name: 'Histogram',
                    type: 'column',
                    data: this.candles.map(candle => {
                        const macd = this.calculateMACD(candle);
                        return {
                            x: candle.time,
                            y: macd ? macd[2] : null
                        };
                    })
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
            const index = this.candles.indexOf(candle);
            if (index < 26) return null; // Need at least 26 periods for MACD

            // Get all prices up to current candle
            const prices = this.candles.slice(0, index + 1).map(c => c.close);
            
            // Calculate EMAs using the standard formula
            const ema12 = this.calculateEMA(prices, 12);
            const ema26 = this.calculateEMA(prices, 26);
            
            // Calculate MACD line
            const macdLine = ema12 - ema26;
            
            // Calculate MACD values for signal line
            const macdValues = [];
            for (let i = 26; i <= index; i++) {
                const periodPrices = this.candles.slice(0, i + 1).map(c => c.close);
                const periodEma12 = this.calculateEMA(periodPrices, 12);
                const periodEma26 = this.calculateEMA(periodPrices, 26);
                macdValues.push(periodEma12 - periodEma26);
            }
            
            // Calculate signal line (9-period EMA of MACD)
            const signalLine = this.calculateEMA(macdValues, 9);
            
            // Calculate histogram
            const histogram = macdLine - signalLine;
            
            return [macdLine, signalLine, histogram];
        },
        calculateEMA(prices, period) {
            if (prices.length < period) return null;
            
            // Calculate multiplier: (2 / (period + 1))
            const multiplier = 2 / (period + 1);
            
            // Calculate SMA for the first EMA value
            let ema = prices.slice(0, period).reduce((sum, price) => sum + price, 0) / period;
            
            // Calculate EMA for remaining values
            for (let i = period; i < prices.length; i++) {
                // EMA = (Close - EMA(previous)) × multiplier + EMA(previous)
                ema = (prices[i] - ema) * multiplier + ema;
            }
            
            return ema;
        },
        calculateBollingerBands(candle) {
            const index = this.candles.indexOf(candle);
            if (index < 20) return null; // Need at least 20 periods for Bollinger Bands

            // Get the last 20 candles up to current candle
            const periodCandles = this.candles.slice(Math.max(0, index - 19), index + 1);
            const prices = periodCandles.map(c => c.close);

            // Calculate 20-period SMA (Middle Band)
            const sma = prices.reduce((sum, price) => sum + price, 0) / prices.length;

            // Calculate standard deviation
            const squaredDifferences = prices.map(price => Math.pow(price - sma, 2));
            const variance = squaredDifferences.reduce((sum, diff) => sum + diff, 0) / prices.length;
            const standardDeviation = Math.sqrt(variance);

            // Calculate Upper and Lower Bands
            const upperBand = sma + (2 * standardDeviation);
            const lowerBand = sma - (2 * standardDeviation);

            return [upperBand, sma, lowerBand];
        },
        calculateSMA(candles, period) {
            if (candles.length < period) return null;
            const lastNCandles = candles.slice(-period);
            const sum = lastNCandles.reduce((acc, candle) => acc + candle.close, 0);
            return sum / period;
        },
        async fetchCryptos() {
            const url = 'https://api.binance.com/api/v3/exchangeInfo';
            try {
                const res = await fetch(url);
                const data = await res.json();
                this.cryptos = data.symbols
                    .filter(symbol => symbol.quoteAsset === 'USDT')
                    .map(symbol => ({
                        name: symbol.baseAsset,
                        symbol: symbol.symbol
                    }));
            } catch (e) {
                console.error('Error fetching cryptocurrencies:', e);
            }
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
        this.fetchCryptos();
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
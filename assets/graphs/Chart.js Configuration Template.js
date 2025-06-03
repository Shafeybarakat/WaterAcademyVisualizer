/**
 * Water Academy - Chart Configuration Template
 * Modern, clean chart implementation inspired by Canva Pro style
 */

// Color theme for all Water Academy visualizations
const waChartColors = {
    primary: ['#3aa5ff', '#54d889', '#a078ea', '#ffba49', '#ff6b6b'],
    status: {
        success: '#54d889',  // Green
        warning: '#ffba49',  // Yellow
        danger: '#ff6b6b',   // Red
        info: '#3aa5ff',     // Blue
        secondary: '#a078ea' // Purple
    },
    background: '#ffffff',
    gridLines: '#f0f0f0'
};

// Font theme for all Water Academy visualizations
const waChartFonts = {
    title: "'Michroma', sans-serif", // For chart titles
    body: "'Ubuntu', sans-serif"    // For ticks, legends, tooltips etc.
};

// Common chart configuration options
const waChartOptions = {
    // Bar chart options
    bar: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(255, 255, 255, 0.9)',
                titleColor: '#333',
                bodyColor: '#333',
                borderColor: '#ddd',
                borderWidth: 1,
                cornerRadius: 8,
                boxPadding: 6,
                usePointStyle: true
            },
            title: {
                display: true,
                font: {
                    size: 16,
                    family: waChartFonts.title,
                    weight: '500'
                },
                padding: {
                    top: 10,
                    bottom: 20
                },
                color: '#333'
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    font: {
                        family: waChartFonts.body
                    }
                }
            },
            y: {
                beginAtZero: true,
                grid: {
                    color: waChartColors.gridLines
                },
                ticks: {
                    precision: 0,
                    font: {
                        family: waChartFonts.body
                    }
                }
            }
        }
    },
    
    // Line chart options
    line: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    usePointStyle: true,
                    padding: 20,
                    font: {
                        family: waChartFonts.body
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(255, 255, 255, 0.9)',
                titleColor: '#333',
                bodyColor: '#333',
                borderColor: '#ddd',
                borderWidth: 1,
                cornerRadius: 8,
                boxPadding: 6,
                usePointStyle: true
            },
            title: { // Added default title config for line charts
                display: true, // Will be overridden by helper if title is empty
                font: {
                    size: 16,
                    family: waChartFonts.title,
                    weight: '500'
                },
                padding: {
                    top: 10,
                    bottom: 20
                },
                color: '#333'
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    font: {
                        family: waChartFonts.body
                    }
                }
            },
            y: {
                beginAtZero: true,
                grid: {
                    color: waChartColors.gridLines
                },
                ticks: {
                    font: {
                        family: waChartFonts.body
                    }
                }
            }
        }
    },
    
    // Doughnut/Pie chart options
    doughnut: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    boxWidth: 12,
                    usePointStyle: true,
                    pointStyle: 'circle',
                    font: {
                        family: waChartFonts.body,
                        size: 13 // Slightly increased for readability with Ubuntu
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(255, 255, 255, 0.9)',
                titleColor: '#333',
                bodyColor: '#333',
                borderColor: '#ddd',
                borderWidth: 1,
                cornerRadius: 8,
                boxPadding: 6,
                usePointStyle: true,
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.raw || 0;
                        const total = context.dataset.data.reduce((acc, curr) => acc + curr, 0);
                        const percentage = Math.round((value / total) * 100);
                        return `${label}: ${value} (${percentage}%)`;
                    }
                }
            },
            title: { // Added default title config for doughnut charts
                display: true, // Will be overridden by helper if title is empty
                font: {
                    size: 16,
                    family: waChartFonts.title,
                    weight: '500'
                },
                padding: {
                    top: 10,
                    bottom: 20
                },
                color: '#333'
            }
        }
    }
};

// Helper functions for chart creation
function createBarChart(canvasId, labels, data, title, colors = waChartColors.primary) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: title,
                data: data,
                backgroundColor: colors,
                borderRadius: 12,
                borderWidth: 0
            }]
        },
        options: {
            ...waChartOptions.bar,
            plugins: {
                ...waChartOptions.bar.plugins,
                title: {
                    ...waChartOptions.bar.plugins.title,
                    text: title,
                    display: !!title // Only display if title text is provided
                }
            }
        }
    });
}

function createLineChart(canvasId, labels, datasets, title) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasets.map((dataset, index) => ({
                label: dataset.label,
                data: dataset.data,
                borderColor: waChartColors.primary[index % waChartColors.primary.length],
                backgroundColor: 'transparent',
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 4,
                pointBackgroundColor: waChartColors.primary[index % waChartColors.primary.length],
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }))
        },
        options: {
            ...waChartOptions.line,
            plugins: {
                ...waChartOptions.line.plugins, // legend, tooltip from base
                title: {
                    ...waChartOptions.line.plugins.title, // base title style
                    text: title, // dynamic text
                    display: !!title // Only display if title text is provided
                }
            }
        }
    });
}

function createDoughnutChart(canvasId, labels, data, title, colors = null) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    const chartColors = colors || waChartColors.primary;
    
    return new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: chartColors,
                borderWidth: 0,
                offset: 5,
                hoverOffset: 10
            }]
        },
        options: {
            ...waChartOptions.doughnut,
            plugins: {
                ...waChartOptions.doughnut.plugins, // legend, tooltip from base
                title: {
                    ...waChartOptions.doughnut.plugins.title, // base title style
                    text: title, // dynamic text
                    display: !!title // Only display if title text is provided
                }
            }
        }
    });
}
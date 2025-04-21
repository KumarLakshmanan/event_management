<?php
if (!isset($_SESSION)) {
    session_start();
}


$today = date('Y-m-d');

?>
<div class="p-3"></div>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
<link rel="stylesheet" href="<?= $baseUrl ?>css/material.css" type="text/css" />
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.8.2/dist/chart.min.js"></script>
<div class="container">
    <div class="row">
        <div class="col-sm-6">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-success shadow-success text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10">today</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize">Today's Page views</p>
                        <h4 class="mb-0">
                            <?php
                            $sql = 'SELECT count(id) as cnt FROM page_views WHERE date = :date';
                            $params = array(
                                ':date' => date('Y-m-d')
                            );
                            $result = $pdoConn->prepare($sql);
                            $result->execute($params);
                            $row = $result->fetch(PDO::FETCH_ASSOC);
                            echo $row['cnt'];
                            ?>
                        </h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-primary shadow-primary text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10">calendar_month</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize">Total Page Views</p>
                        <h4 class="mb-0">
                            <?php
                            $sql = 'SELECT count(id) as cnt FROM page_views';
                            $result = $pdoConn->prepare($sql);
                            $result->execute();
                            $row = $result->fetch(PDO::FETCH_ASSOC);
                            echo $row['cnt'];
                            ?>
                        </h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-success shadow-success text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10">call</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize">Today's Contact Messages</p>
                        <h4 class="mb-0">
                            <?php
                            $sql = "SELECT count(id) as cnt FROM contact_forms WHERE created_at LIKE '$today%'";
                            $stmt = $pdoConn->prepare($sql);
                            $stmt->execute();
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            echo $result['cnt'];
                            ?>
                        </h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-primary shadow-primary text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10">call</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize">Total Contacts Messages</p>
                        <h4 class="mb-0">
                            <?php
                            $sql = "SELECT count(id) as cnt FROM contact_forms";
                            $stmt = $pdoConn->prepare($sql);
                            $stmt->execute();
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            echo $result['cnt'];
                            ?>
                        </h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
            </div>
        </div>
    </div>
    <?php
    $sql = "SELECT count(id) as cnt, device FROM page_views WHERE date >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY device ORDER BY cnt DESC";
    $stmt = $pdoConn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalDevices = 0;
    $others = 0;
    $i = 0;
    foreach ($result as $row) {
        $totalDevices += $row['cnt'];
        if ($i > 5) {
            $others += $row['cnt'];
        }
        $i++;
    }
    if (count($result) > 0) {
    ?>
        <div class="row">
            <div class="col-md-8">
                <div class="card p-2 h-100">
                    <canvas id="myChart" width="400" height="250"></canvas>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <h6 class="text-center mt-3" style="font-size: 12px;">Devices Analytics</h6>
                    <div class="card-body px-2">
                        <?php
                        for ($i = 0; $i < count($result); $i++) {
                            if ($i > 5) {
                                $result[$i]['device'] = 'Others';
                                $result[$i]['cnt'] = $others;
                                $percentage = ($result[$i]['cnt'] / $totalDevices) * 100;
                                $percentage = round($percentage, 2);
                                $result[$i]['percentage'] = $percentage;
                            } else {
                                $row = $result[$i];
                                $percentage = ($row['cnt'] / $totalDevices) * 100;
                                $percentage = round($percentage, 2);
                                $result[$i]['percentage'] = $percentage;
                            }
                        ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="progress-container w-100">
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: <?= $result[$i]['percentage'] ?>%;"></div>
                                    </div>
                                </div>
                                <span class="text-sm ps-2"><?= $result[$i]['percentage'] ?>%</span>
                            </div>
                            <div class="text-left">
                                <p class="text-sm"><?= $result[$i]['device'] ?> (<?= $result[$i]['cnt'] ?> Views)</p>
                            </div>
                        <?php
                            if ($i > 5) {
                                break;
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }
    ?>
</div>
<style>
    body,
    html {
        height: 100%;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .uploadContainer {
        width: 90%;
        max-width: 600px;
        margin: 0 auto;
    }
</style>
<script>
    const ctx = document.getElementById('myChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: [
                <?php
                $days = [];
                for ($i = 6; $i >= 0; $i--) {
                    $days[] = date('Y-m-d', strtotime("-$i days"));
                }
                $views = [];
                foreach ($days as $day) {
                    $sql = "SELECT count(id) as cnt FROM page_views WHERE date LIKE '%$day%'";
                    $stmt = $pdoConn->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $views[] = $result['cnt'];
                }
                echo '"' . implode('","', $days) . '"';
                ?>
            ],
            datasets: [{
                label: 'Views',
                data: [
                    <?php
                    echo implode(',', $views);
                    ?>
                ],
                borderColor: '#48a44c',
                backgroundColor: '#58ca5d',
                pointStyle: 'crossRot',
                pointRadius: 10,
                pointHoverRadius: 15
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false,
                },
                title: {
                    display: true,
                    text: (ctx) => 'Views Analytics',
                }
            },
            scales: {
                y: {
                    grid: {
                        drawBorder: false,
                        display: true,
                        drawOnChartArea: true,
                        drawTicks: false,
                        borderDash: [5, 5],
                        color: 'rgba(255, 255, 255, .2)'
                    },
                    ticks: {
                        suggestedMin: 0,
                        suggestedMax: 500,
                        beginAtZero: true,
                        padding: 10,
                        font: {
                            size: 14,
                            weight: 300,
                            family: "Roboto",
                            style: 'normal',
                            lineHeight: 2
                        },
                    },
                },
                x: {
                    grid: {
                        drawBorder: false,
                        display: true,
                        drawOnChartArea: true,
                        drawTicks: false,
                        borderDash: [5, 5],
                        color: 'rgba(255, 255, 255, .2)'
                    },
                    ticks: {
                        display: true,
                        padding: 10,
                        font: {
                            size: 14,
                            weight: 300,
                            family: "Roboto",
                            style: 'normal',
                            lineHeight: 2
                        },
                    }
                },
            },
        }
    });
    const ctx2 = document.getElementById('myChart2').getContext('2d');
    new Chart(ctx2, {
        type: "bar",
        data: {
            datasets: [{
                label: "Android Users",
                tension: 0.4,
                borderWidth: 0,
                borderRadius: 4,
                borderSkipped: false,
                backgroundColor: "#217aea",
                data: [
                    <?php
                    for ($i = 6; $i >= 0; $i--) {
                        $today = date('Y-m-d');
                        $date = date('Y-m-d', strtotime($today . ' -' . $i . ' days'));
                        $sql = "SELECT count(id) as cnt FROM users WHERE created_at LIKE '$date%'";
                        $stmt = $pdoConn->prepare($sql);
                        $stmt->execute();
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        echo $result['cnt'] . ',';
                    }
                    ?>
                ],
                maxBarThickness: 100
            }, ],
            labels: [
                <?php
                for ($i = 6; $i >= 0; $i--) {
                    $today = date('Y-m-d');
                    $date = date('Y-m-d', strtotime($today . ' -' . $i . ' days'));
                    echo '"' . date('D', strtotime($date)) . '",';
                }
                ?>
            ]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: (ctx) => 'Users Analytics',
                },
                legend: {
                    display: false,
                }
            },
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            scales: {
                y: {
                    grid: {
                        drawBorder: false,
                        display: true,
                        drawOnChartArea: true,
                        drawTicks: false,
                        borderDash: [5, 5],
                        color: 'rgba(255, 255, 255, .2)'
                    },
                    ticks: {
                        suggestedMin: 0,
                        suggestedMax: 500,
                        beginAtZero: true,
                        padding: 10,
                        font: {
                            size: 14,
                            weight: 300,
                            family: "Roboto",
                            style: 'normal',
                            lineHeight: 2
                        },
                    },
                },
                x: {
                    grid: {
                        drawBorder: false,
                        display: true,
                        drawOnChartArea: true,
                        drawTicks: false,
                        borderDash: [5, 5],
                        color: 'rgba(255, 255, 255, .2)'
                    },
                    ticks: {
                        display: true,
                        padding: 10,
                        font: {
                            size: 14,
                            weight: 300,
                            family: "Roboto",
                            style: 'normal',
                            lineHeight: 2
                        },
                    }
                },
            },
        },
    });
    window.addEventListener("resize", () => console.log('window width', detectMob()));
</script>
<style>
    #myChart {
        height: 100% !important;
    }
</style>
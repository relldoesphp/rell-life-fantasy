rlf.makeOlineChart = function(chartData, id){
    var ctx = document.getElementById(id+"-canvas").getContext('2d');
        var options = {
            type: 'bar',
            data: chartData,
            options: {
                layout: {
                    padding: {
                        left: 0,
                        right: 0,
                        top: 50,
                        bottom: 0
                    },
                    margin: {
                        left: 0,
                        right: 0,
                        top: 30,
                        bottom: 0
                    }
                },
                legend: {
                    display: false,
                    labels: {
                        fontColor: 'rgba(174, 3, 230)'
                    },
                    position: 'bottom'
                },
                animation: {
                    duration: 250 * 1.5,
                    easing: 'easeInQuad',
                        "onComplete": function() {
                            var chartInstance = this.chart,
                                ctx = chartInstance.ctx;

                            //ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontSize, Chart.defaults.global.defaultFontFamily);
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'bottom';
                            ctx.fillStyle = "purple";
                            ctx.textBaseline = 'bottom';
                            ctx.fontSize = "14";

                            this.data.datasets.forEach(function(dataset, i) {
                                var meta = chartInstance.controller.getDatasetMeta(i);
                                meta.data.forEach(function(bar, index) {
                                    if (dataset.metrics[index] != "" && $("body").hasClass("mobile") === false && dataset.metrics[index] != null) {
                                        var data = dataset.metrics[index]+'\n'+dataset.ordinals[index]+'';
                                        ctx.fillText(data, bar._model.x, bar._model.y - 7);
                                    }
                                });
                            });
                        }
                },
                // "animation": {
                //     "duration": 5,
                //     "onProgress": function(animation) {
                //         progress.value = animation.animationObject.currentStep / animation.animationObject.numSteps;
                //     },
                //     "onComplete": function() {
                //         var chartInstance = this.chart,
                //             ctx = chartInstance.ctx;
                //
                //         //ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontSize, Chart.defaults.global.defaultFontFamily);
                //         ctx.textAlign = 'center';
                //         ctx.textBaseline = 'bottom';
                //         ctx.fillStyle = "purple";
                //         ctx.textBaseline = 'bottom';
                //         ctx.fontSize = "14";
                //
                //         this.data.datasets.forEach(function(dataset, i) {
                //             var meta = chartInstance.controller.getDatasetMeta(i);
                //             meta.data.forEach(function(bar, index) {
                //                 if (dataset.metrics[index] != "" && $("body").hasClass("mobile") === false && dataset.metrics[index] != null) {
                //                     var data = dataset.metrics[index]+'\n'+dataset.ordinals[index]+'';
                //                     ctx.fillText(data, bar._model.x, bar._model.y - 7);
                //                 }
                //             });
                //         });
                //     }
                // },
                scales: {
                    xAxes: [{
                        display: true,
                        stacked: true,
                        scaleLabel: {
                            display: true
                        }
                    }, {
                        id: 'invoice-time',
                        type: 'linear',
                        display: false,
                        stacked: false,
                        scaleLabel: {
                            display: false,
                            labelString: 'D-line'
                        },
                        ticks: {
                            beginAtZero: true,
                            stepSize: 1,
                            max: 20
                        }
                    }],
                    yAxes: [{
                        display: true,
                        stacked: true,
                        scaleLabel: {
                            display: false,
                            labelString: ''
                        },
                        ticks: {
                            beginAtZero: true,
                            max: 100
                        }

                    }, {
                        id: 'invoice-amount',
                        display: false,
                        stacked: false,
                        scaleLabel: {
                            display: false,
                            labelString: 'Dollar Amount'
                        },
                        ticks: {
                            beginAtZero: true,
                            max: 100
                        }
                    }]
                }
            }
        };
    window.myMixedChart = new Chart(ctx, options);
};



rlf.initMatchup = function(off, def, id) {
    var elementId = '#'+id;
    var oline = off.oline;
    var dline = def.dfront;
    var lb = def.LB;

    var runData = [];

    var fourThree = {
        "LT":2.5,
        "LG":7,
        "C": 12,
        "RG": 16,
        "RT": 19
    };

    var threeFour = {
        "LT":1,
        "LG":5,
        "C": 10,
        "RG": 14.5,
        "RT": 19
    };

    var defPos = {};

    if (def.def_base === "3-4" || def.team === "SF" || def.team === "ATL" || def.team === "OAK") {
        defPos = threeFour;
    }


    if (def.def_base === "4-3" && def.team !== "SF" && def.team !== "ATL" && def.team !== "OAK") {
        defPos = fourThree;
        dline.RT.metrics.runStuff = null;
        dline.RT.metrics.passRush = null;
    }

    runData[id] = {
            labels: [
                "",
                oline.LT.last_name+' #'+oline.LT.team_info.number+" "+oline.LT.status,
                oline.LG.last_name+' #'+oline.LG.team_info.number+" "+oline.LG.status,
                oline.C.last_name+' #'+oline.C.team_info.number+" "+oline.C.status,
                oline.RG.last_name+' #'+oline.RG.team_info.number+" "+oline.RG.status,
                oline.RT.last_name+' #'+oline.RT.team_info.number+" "+oline.RT.status,
                ""
            ],
            datasets: [{
                type: 'bar',
                label: 'Offensive Line',
                radius: 50,
                data: [
                    "",
                    oline.LT.metrics.runBlock,
                    oline.LG.metrics.runBlock,
                    Math.round((oline.C.percentiles.speedScore * .4) + (oline.C.metrics.runBlock * .6)),
                    oline.RG.metrics.runBlock,
                    oline.RT.metrics.runBlock,
                    ""
                ],
                ordinals: [
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    ""
                ],
                metrics: [
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    ""
                ],
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }, {
                type: 'scatter',
                label: 'Defensive Line',
                radius: 45,
                data: [
                    {
                        "y":dline.LT.metrics.runStuff,
                        "x":defPos["LT"]
                    },
                    {
                        "y":dline.LG.metrics.runStuff,
                        "x":defPos["LG"]
                    },
                    {
                        "y":dline.C.metrics.runStuff,
                        "x":defPos["C"]
                    },
                    {
                        "y":dline.RG.metrics.runStuff,
                        "x":defPos["RG"]
                    },
                    {
                        "y":dline.RT.metrics.runStuff,
                        "x":defPos["RT"]
                    }
                ],
                ordinals: [
                    "",
                    "",
                    "",
                    "",
                    ""
                ],
                metrics: [
                    dline.LT.last_name+' #'+dline.LT.team_info.number,//3
                    dline.LG.last_name+' #'+dline.LG.team_info.number,//1
                    dline.C.last_name+' #'+dline.C.team_info.number,//1
                    dline.RG.last_name+' #'+dline.RG.team_info.number,//3
                    dline.RT.last_name+' #'+dline.RT.team_info.number
                ],
                xAxisID: 'invoice-time',
                yAxisID: 'invoice-amount',
                backgroundColor: 'rgba(75, 00, 150, 0.2)',
                borderColor: 'rgba(75, 00, 150,1)',
                borderWidth: 2
            }]
        };


    var passData = [];
    passData[id] = {
        labels: [
            "",
            oline.LT.last_name+'-LT'+" "+oline.LT.status,
            oline.LG.last_name+'-LG'+" "+oline.LG.status,
            oline.C.last_name+'-C'+" "+oline.C.status,
            oline.RG.last_name+'-RG'+" "+oline.RG.status,
            oline.RT.last_name+'-RT'+" "+oline.RT.status,
            ""
        ],
        datasets: [
            {
                type: 'bar',
                label: 'Offensive Line',
                radius: 40,
                fill: false,
                data: [
                    "",
                    oline.LT.metrics.edgeBlock,
                    oline.LG.metrics.passBlock,
                    oline.C.metrics.passBlock,
                    oline.RG.metrics.passBlock,
                    oline.RT.metrics.edgeBlock,
                    ""
                ],
                ordinals: [
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                ],
                metrics: [
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    ""
                ],
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            },
            {
                type: 'line',
                stack: 'Stack One',
                label: 'Defensive Line',
                radius: 45,
                fill: true,
                data: [
                    {
                        "y":dline.LT.metrics.edgeRush,
                        "x":defPos["LT"]
                    },
                    {
                        "y":dline.LG.metrics.passRush,
                        "x":defPos["LG"]
                    },
                    {
                        "y":dline.C.percentiles.passRush,
                        "x":defPos["C"]
                    },
                    {
                        "y":dline.RG.metrics.passRush,
                        "x":defPos["RG"]
                    },
                    {
                        "y":dline.RT.metrics.passRush,
                        "x":defPos["RT"]
                    }
                ],
                ordinals: [
                    "",
                    "",
                    "",
                    "",
                    ""
                ],
                metrics: [
                    dline.LT.last_name,
                    dline.LG.last_name,
                    dline.C.last_name,
                    dline.RG.last_name,
                    dline.RT.last_name
                ],
                xAxisID: 'invoice-time',
                yAxisID: 'invoice-amount',
                backgroundColor: 'rgba(75, 00, 150, 0.2)',
                borderColor: 'rgba(75, 00, 150,1)',
                borderWidth: 2
            }]
    };
    rlf.makeOlineChart(runData[id], id);
    $("#team1-runBlock").on("click", function() {
        var context1 = document.querySelector('#team1-canvas').getContext('2d');
        rlf.makeOlineChart(runData[id], id);
        $(this).addClass("dark");
        $("#passBlock").removeClass("dark");
    });
    $("#team1-passBlock").on("click", function() {
        var context1 = document.querySelector('#team1-canvas').getContext('2d');
        rlf.makeOlineChart(passData[id], id);
        $(this).addClass("dark");
        $("#runBlock").removeClass("dark");
    });
    $("#team2-runBlock").on("click", function() {
        console.log("triggered");
        var context1 = document.querySelector('#team2-canvas').getContext('2d');
        rlf.makeOlineChart(runData[id], id);
        $(this).addClass("dark");
        $("#passBlock").removeClass("dark");
    });
    $("#team2-passBlock").on("click", function() {
        var context1 = document.querySelector('#team2-canvas').getContext('2d');
        rlf.makeOlineChart(passData[id], id);
        $(this).addClass("dark");
        $("#runBlock").removeClass("dark");
    });

    var metricsDefault = "Not Available<div class='progress'><div class='determinate' style='width:0%'></div></div>";

    var LWR = off.depth_chart.LWR[1];
    var RCB = def.depth_chart.RCB[1];

    $('#'+id+'-LWR').DataTable({
        "paging": false,
        "ordering": false,
        "searching": false,
        "columns": [
            {title: "", data: "name", "defaultContent":0},
            {title: "<img class='responsive-img' style='width:100px' src='https://sleepercdn.com/content/nfl/players/"+LWR.sleeper_id+".jpg'/><br><a href='/player/"+LWR.sleeper_id+"'>"+LWR.first_name+" "+LWR.last_name+" <i class='red-text'>"+LWR.status+"</i></a>", data: "metric1", "defaultContent":metricsDefault, className: "dt-center", targets: "_all"},
            {title: "<img class='responsive-img' style='width:100px' src='https://sleepercdn.com/content/nfl/players/"+RCB.sleeper_id+".jpg'/><br>"+RCB.first_name+" "+RCB.last_name+" <i class='red-text'>"+RCB.status+"</i>", data: "metric2", "defaultContent":metricsDefault, className: "dt-center", targets: "_all"},
        ],
        data:[
            {
                "name": "Speed",
                "metric1": LWR.metrics.fortyTime+"<div class='progress'><div class='determinate' style='width:"+LWR.percentiles.fortyTime+"%'></div></div>",
                "metric2": RCB.metrics.fortyTime+"<div class='progress'><div class='determinate' style='width:"+RCB.percentiles.fortyTime+"%'></div></div>",
            },
            {
                "name": "JumpBall",
                "metric1": LWR.metrics.jumpball+"<div class='progress'><div class='determinate' style='width:"+LWR.percentiles.jumpball+"%'></div></div>",
                "metric2": RCB.metrics.jumpball+"<div class='progress'><div class='determinate' style='width:"+RCB.percentiles.jumpball+"%'></div></div>",
            },
            {
                "name": "Agility",
                "metric1": LWR.metrics.routeAgility+"<div class='progress'><div class='determinate' style='width:"+LWR.percentiles.routeAgility+"%'></div></div>",
                "metric2": RCB.metrics.agility+"<div class='progress'><div class='determinate' style='width:"+RCB.percentiles.agility+"%'></div></div>",
            },
            {
                "name": "Bully",
                "metric1": LWR.metrics.bully+"<div class='progress'><div class='determinate' style='width:"+LWR.percentiles.bully+"%'></div></div>",
                "metric2": RCB.metrics.bully+"<div class='progress'><div class='determinate' style='width:"+RCB.percentiles.bully+"%'></div></div>",
            }
        ]
    });

    var LWR = off.depth_chart.RWR[1];
    var RCB = def.depth_chart.LCB[1];

    $('#'+id+'-RWR').DataTable({
        "paging": false,
        "ordering": false,
        "searching": false,
        "columns": [
            {title: "", data: "name", "defaultContent":0},
            {title: "<img class='responsive-img' style='width:100px' src='https://sleepercdn.com/content/nfl/players/"+LWR.sleeper_id+".jpg'/><br>"+LWR.first_name+" "+LWR.last_name+" <i class='red-text'>"+LWR.status+"</i>", data: "metric1", "defaultContent":metricsDefault, className: "dt-center", targets: "_all"},
            {title: "<img class='responsive-img' style='width:100px' src='https://sleepercdn.com/content/nfl/players/"+RCB.sleeper_id+".jpg'/><br>"+RCB.first_name+" "+RCB.last_name+" <i class='red-text'>"+RCB.status+"</i>", data: "metric2", "defaultContent":metricsDefault, className: "dt-center", targets: "_all"},
        ],
        data:[
            {
                "name": "Speed",
                "metric1": LWR.metrics.fortyTime+"<div class='progress'><div class='determinate' style='width:"+LWR.percentiles.fortyTime+"%'></div></div>",
                "metric2": RCB.metrics.fortyTime+"<div class='progress'><div class='determinate' style='width:"+RCB.percentiles.fortyTime+"%'></div></div>",
            },
            {
                "name": "JumpBall",
                "metric1": LWR.metrics.jumpball+"<div class='progress'><div class='determinate' style='width:"+LWR.percentiles.jumpball+"%'></div></div>",
                "metric2": RCB.metrics.jumpball+"<div class='progress'><div class='determinate' style='width:"+RCB.percentiles.jumpball+"%'></div></div>",
            },
            {
                "name": "Agility",
                "metric1": LWR.metrics.routeAgility+"<div class='progress'><div class='determinate' style='width:"+LWR.percentiles.routeAgility+"%'></div></div>",
                "metric2": RCB.metrics.agility+"<div class='progress'><div class='determinate' style='width:"+RCB.percentiles.agility+"%'></div></div>",
            },
            {
                "name": "Bully",
                "metric1": LWR.metrics.bully+"<div class='progress'><div class='determinate' style='width:"+LWR.percentiles.bully+"%'></div></div>",
                "metric2": RCB.metrics.bully+"<div class='progress'><div class='determinate' style='width:"+RCB.percentiles.bully+"%'></div></div>",
            }
        ]
    });

    var LWR = off.depth_chart.SWR[1];
    var RCB = def.CB.slot;

    $('#'+id+'-SWR').DataTable({
        "paging": false,
        "ordering": false,
        "searching": false,
        "columns": [
            {title: "", data: "name", "defaultContent":0},
            {title: "<img class='responsive-img' style='width:100px' src='https://sleepercdn.com/content/nfl/players/"+LWR.sleeper_id+".jpg'/><br>"+LWR.first_name+" "+LWR.last_name+" <i class='red-text'>"+LWR.status+"</i>", data: "metric1", "defaultContent":metricsDefault, className: "dt-center", targets: "_all"},
            {title: "<img class='responsive-img' style='width:100px' src='https://sleepercdn.com/content/nfl/players/"+RCB.sleeper_id+".jpg'/><br>"+RCB.first_name+" "+RCB.last_name+" <i class='red-text'>"+RCB.status+"</i>", data: "metric2", "defaultContent":metricsDefault, className: "dt-center", targets: "_all"},
        ],
        data:[
            {
                "name": "Speed",
                "metric1": LWR.metrics.fortyTime+"<div class='progress'><div class='determinate' style='width:"+LWR.percentiles.fortyTime+"%'></div></div>",
                "metric2": RCB.metrics.fortyTime+"<div class='progress'><div class='determinate' style='width:"+RCB.percentiles.fortyTime+"%'></div></div>",
            },
            {
                "name": "JumpBall",
                "metric1": LWR.metrics.jumpball+"<div class='progress'><div class='determinate' style='width:"+LWR.percentiles.jumpball+"%'></div></div>",
                "metric2": RCB.metrics.jumpball+"<div class='progress'><div class='determinate' style='width:"+RCB.percentiles.jumpball+"%'></div></div>",
            },
            {
                "name": "Agility",
                "metric1": LWR.metrics.routeAgility+"<div class='progress'><div class='determinate' style='width:"+LWR.percentiles.routeAgility+"%'></div></div>",
                "metric2": RCB.metrics.agility+"<div class='progress'><div class='determinate' style='width:"+RCB.percentiles.agility+"%'></div></div>",
            },
            {
                "name": "Bully",
                "metric1": LWR.metrics.bully+"<div class='progress'><div class='determinate' style='width:"+LWR.percentiles.bully+"%'></div></div>",
                "metric2": RCB.metrics.bully+"<div class='progress'><div class='determinate' style='width:"+RCB.percentiles.bully+"%'></div></div>",
            }
        ]
    });


    var TE = off.depth_chart.TE[1];
    var MLB = def.LBs.middle[0];
    $('#'+id+'-TE').DataTable({
        "paging": false,
        "ordering": false,
        "searching": false,
        "columns": [
            {title: "", data: "name", "defaultContent":0},
            {title: "<img class='responsive-img' style='width:100px' src='https://sleepercdn.com/content/nfl/players/"+TE.sleeper_id+".jpg'/><br>"+TE.first_name+" "+TE.last_name+" <i class='red-text'>"+TE.status+"</i>", data: "metric1", "defaultContent":metricsDefault, className: "dt-center", targets: "_all"},
            {title: "<img class='responsive-img' style='width:100px' src='https://sleepercdn.com/content/nfl/players/"+MLB.sleeper_id+".jpg'/><br>"+MLB.first_name+" "+MLB.last_name+" <i class='red-text'>"+MLB.status+"</i>", data: "metric2", "defaultContent":metricsDefault, className: "dt-center", targets: "_all"},
        ],
        data:[
            {
                "name": "Speed",
                "metric1": TE.metrics.fortyTime+"<div class='progress'><div class='determinate' style='width:"+TE.percentiles.fortyTime+"%'></div></div>",
                "metric2": MLB.metrics.fortyTime+"<div class='progress'><div class='determinate' style='width:"+MLB.percentiles.fortyTime+"%'></div></div>",
            },
            {
                "name": "JumpBall",
                "metric1": TE.metrics.jumpball+"<div class='progress'><div class='determinate' style='width:"+TE.percentiles.jumpball+"%'></div></div>",
                "metric2": MLB.metrics.jumpball+"<div class='progress'><div class='determinate' style='width:"+MLB.percentiles.jumpball+"%'></div></div>",
            },
            {
                "name": "Agility",
                "metric1": TE.metrics.agility+"<div class='progress'><div class='determinate' style='width:"+TE.percentiles.agility+"%'></div></div>",
                "metric2": MLB.metrics.agility+"<div class='progress'><div class='determinate' style='width:"+MLB.percentiles.agility+"%'></div></div>",
            },
            {
                "name": "Block",
                "metric1": TE.metrics.runBlock+"<div class='progress'><div class='determinate' style='width:"+TE.percentiles.runBlock+"%'></div></div>",
                "metric2": MLB.metrics.bully+"<div class='progress'><div class='determinate' style='width:"+MLB.percentiles.bully+"%'></div></div>",
            }
        ]
    });

    var FS = def.depth_chart.FS[1];
    $('#'+id+'-FS').DataTable({
        "paging": false,
        "ordering": false,
        "searching": false,
        "info": false,
        "columns": [
            {title: "<img class='responsive-img' style='width:100px' src='https://sleepercdn.com/content/nfl/players/"+FS.sleeper_id+".jpg'/><br>"+FS.first_name+" "+FS.last_name+" <i class='red-text'>"+FS.status+"</i>", data: "metric1", "defaultContent":metricsDefault, className: "dt-center", targets: "_all"}
        ],
        data:[
            {
                "metric1": "Speed -"+FS.metrics.fortyTime+"<div class='progress'><div class='determinate' style='width:"+FS.percentiles.fortyTime+"%'></div></div>",
            },
            {
                "metric1": "Jumpball -"+FS.metrics.jumpball+"<div class='progress'><div class='determinate' style='width:"+FS.percentiles.jumpball+"%'></div></div>",
            },
            {
                "metric1": "Agility -"+FS.metrics.agility+"<div class='progress'><div class='determinate' style='width:"+FS.percentiles.agility+"%'></div></div>",
            }
        ]
    });


    var SS = def.depth_chart.SS[1];
    $('.'+id+'-SS').DataTable({
        "paging": false,
        "ordering": false,
        "searching": false,
        "info":false,
        "columns": [
            {title: "<img class='responsive-img' style='width:100px' src='https://sleepercdn.com/content/nfl/players/"+SS.sleeper_id+".jpg'/><br>"+SS.first_name+" "+SS.last_name+" <i class='red-text'>"+SS.status+"</i>", data: "metric1", "defaultContent":metricsDefault, className: "dt-center", targets: "_all"}
        ],
        data:[
            {
                "metric1": "Speed -"+SS.metrics.fortyTime+"<div class='progress'><div class='determinate' style='width:"+SS.percentiles.fortyTime+"%'></div></div>",
            },
            {
                "metric1": "Jumpball -"+SS.metrics.jumpball+"<div class='progress'><div class='determinate' style='width:"+SS.percentiles.jumpball+"%'></div></div>",
            },
            {
                "metric1": "Agility -"+SS.metrics.agility+"<div class='progress'><div class='determinate' style='width:"+SS.percentiles.agility+"%'></div></div>",
            }
        ]
    });

    // $("."+id+"-RBs").html(' ' +
    //     '<div class="col s12">\n' +
    //     '  <ul class="'+id+'-tabs tabs">\n' +
    //     '    <li class="tab col s3"><a class="active" href="#'+id+'-RB1-container">RB1</a></li>\n' +
    //     '    <li class="tab col s3"><a href="#'+id+'-RB2-container">RB2</a></li>\n' +
    //     '  </ul>\n' +
    //     '</div>\n' +
    //     '<div id="#'+id+'-RB1-container" class="col s12">\n' +
    //     '  <h5 class="center">RB1</h5>\n' +
    //     '  <table id="'+id+'-RB1" width="100%"></table>\n' +
    //     '</div>\n' +
    //     '<div id="#'+id+'-RB2-container" class="col s12">\n' +
    //     '  <h5 class="center">RB2</h5>\n' +
    //     '  <table id="'+id+'-RB2" width="100%"></table>\n' +
    //     '</div>');

    var RB1 = off.depth_chart.RB[1];
    $('#'+id+'-RB1').DataTable({
        "paging": false,
        "ordering": false,
        "searching": false,
        "info":false,
        "columns": [
            {title: "<img class='responsive-img' style='width:100px' src='https://sleepercdn.com/content/nfl/players/"+RB1.sleeper_id+".jpg'/><br>"+RB1.first_name+" "+RB1.last_name+" <i class='red-text'>"+RB1.status+"</i>", data: "metric1", "defaultContent":metricsDefault, className: "dt-center", targets: "_all"}
        ],
        data:[
            {
                "metric1": "Speed-"+RB1.metrics.fortyTime+"<div class='progress'><div class='determinate' style='width:"+RB1.percentiles.fortyTime+"%'></div></div>",
            },
            {
                "metric1": "Power-"+RB1.metrics.power+"<div class='progress'><div class='determinate' style='width:"+RB1.percentiles.power+"%'></div></div>",
            },
            {
                "metric1": "Elusiveness-"+RB1.metrics.elusiveness+"<div class='progress'><div class='determinate' style='width:"+RB1.percentiles.elusiveness+"%'></div></div>",
            },
            {
                "metric1": "Juke Agility-"+RB1.metrics.jukeAgility+"<div class='progress'><div class='determinate' style='width:"+RB1.percentiles.jukeAgility+"%'></div></div>",
            },
        ]
    });

    var RB1 = off.depth_chart.RB[2];
    $('#'+id+'-RB2').DataTable({
        "paging": false,
        "ordering": false,
        "searching": false,
        "info": false,
        "columns": [
            {title: "<img class='responsive-img' style='width:100px' src='https://sleepercdn.com/content/nfl/players/"+RB1.sleeper_id+".jpg'/><br>"+RB1.first_name+" "+RB1.last_name+" <i class='red-text'>"+RB1.status+"</i>", data: "metric1", "defaultContent":metricsDefault, className: "dt-center", targets: "_all"}
        ],
        data:[
            {
                "metric1": "Speed-"+RB1.metrics.fortyTime+"<div class='progress'><div class='determinate' style='width:"+RB1.percentiles.fortyTime+"%'></div></div>",
            },
            {
                "metric1": "Power-"+RB1.metrics.power+"<div class='progress'><div class='determinate' style='width:"+RB1.percentiles.power+"%'></div></div>",
            },
            {
                "metric1": "Elusiveness-"+RB1.metrics.elusiveness+"<div class='progress'><div class='determinate' style='width:"+RB1.percentiles.elusiveness+"%'></div></div>",
            },
            {
                "metric1": "Juke Agility-"+RB1.metrics.jukeAgility+"<div class='progress'><div class='determinate' style='width:"+RB1.percentiles.jukeAgility+"%'></div></div>",
            }
        ]
    });

   // $("."+id+"-tabs").tabs();

    var LBs = def.LBs;
    $('#'+id+'-LBs').DataTable({
        "paging": false,
        "ordering": false,
        "searching": false,
        "info":false,
        "columns": [
            {title: "", data: "name", "defaultContent":0},
            {title: "<img class='responsive-img' style='width:100px' src='https://sleepercdn.com/content/nfl/players/"+LBs.middle[0].sleeper_id+".jpg'/><br>"+LBs.middle[0].first_name+" "+LBs.middle[0].last_name+" <i class='red-text'>"+LBs.middle[0].status+"</i>", data: "metric1", "defaultContent":metricsDefault, className: "dt-center", targets: "_all"},
            {title: "<img class='responsive-img' style='width:100px' src='https://sleepercdn.com/content/nfl/players/"+LBs.weak[0].sleeper_id+".jpg'/><br>"+LBs.weak[0].first_name+" "+LBs.weak[0].last_name+" <i class='red-text'>"+LBs.weak[0].status+"</i>", data: "metric2", "defaultContent":metricsDefault, className: "dt-center", targets: "_all"},
        ],
        data:[
            {
                "name": "Speed",
                "metric1": LBs.middle[0].metrics.fortyTime+"<div class='progress'><div class='determinate' style='width:"+LBs.middle[0].percentiles.fortyTime+"%'></div></div>",
                "metric2": LBs.weak[0].metrics.fortyTime+"<div class='progress'><div class='determinate' style='width:"+LBs.weak[0].percentiles.fortyTime+"%'></div></div>",
            },
            {
                "name": "Agility",
                "metric1": LBs.middle[0].metrics.agility+"<div class='progress'><div class='determinate' style='width:"+LBs.middle[0].percentiles.agility+"%'></div></div>",
                "metric2": LBs.weak[0].metrics.agility+"<div class='progress'><div class='determinate' style='width:"+LBs.weak[0].percentiles.agility+"%'></div></div>",
            },
            {
                "name": "Power",
                "metric1": LBs.middle[0].metrics.power+"<div class='progress'><div class='determinate' style='width:"+LBs.middle[0].percentiles.power+"%'></div></div>",
                "metric2": LBs.weak[0].metrics.power+"<div class='progress'><div class='determinate' style='width:"+LBs.weak[0].percentiles.power+"%'></div></div>",
            }
        ]
    });


    var WR1 = off.depth_chart.LWR[1];
    $('#'+id+'-WR1').DataTable({
        "paging": false,
        "ordering": false,
        "searching": false,
        "info":     false,
        "columns": [
            {title: "<img class='responsive-img' style='width:100px' src='https://sleepercdn.com/content/nfl/players/"+WR1.sleeper_id+".jpg'/><br>"+WR1.first_name+" "+WR1.last_name+" <i class='red-text'>"+WR1.status+"</i>", data: "metric1", "defaultContent":metricsDefault, className: "dt-center", targets: "_all"}
        ],
        data:[
            {
                "metric1": "Speed-"+WR1.metrics.fortyTime+"<div class='progress'><div class='determinate' style='width:"+WR1.percentiles.fortyTime+"%'></div></div>",
            },
            {
                "metric1": "Jumpball-"+WR1.metrics.jumpball+"<div class='progress'><div class='determinate' style='width:"+WR1.percentiles.jumpball+"%'></div></div>",
            },
            {
                "metric1": "Route Agility-"+WR1.metrics.routeAgility+"<div class='progress'><div class='determinate' style='width:"+WR1.percentiles.routeAgility+"%'></div></div>",
            },
            {
                "metric1": "Bully-"+WR1.metrics.bully+"<div class='progress'><div class='determinate' style='width:"+WR1.percentiles.bully+"%'></div></div>",
            }
        ]
    });

    var WR1 = off.depth_chart.RWR[1];
    $('#'+id+'-WR2').DataTable({
        "paging": false,
        "ordering": false,
        "searching": false,
        "info":     false,
        "columns": [
            {title: "<img class='responsive-img' style='width:100px' src='https://sleepercdn.com/content/nfl/players/"+WR1.sleeper_id+".jpg'/><br>"+WR1.first_name+" "+WR1.last_name, data: "metric1", "defaultContent":metricsDefault, className: "dt-center", targets: "_all"}
        ],
        data:[
            {
                "metric1": "Speed-"+WR1.metrics.fortyTime+"<div class='progress'><div class='determinate' style='width:"+WR1.percentiles.fortyTime+"%'></div></div>",
            },
            {
                "metric1": "Jumpball-"+WR1.metrics.jumpball+"<div class='progress'><div class='determinate' style='width:"+WR1.percentiles.jumpball+"%'></div></div>",
            },
            {
                 "metric1": "Route Agility-"+WR1.metrics.routeAgility+"<div class='progress'><div class='determinate' style='width:"+WR1.percentiles.routeAgility+"%'></div></div>",
            },
            {
                "metric1": "Bully-"+WR1.metrics.bully+"<div class='progress'><div class='determinate' style='width:"+WR1.percentiles.bully+"%'></div></div>",
            }
        ]
    });

    var WR1 = off.depth_chart.SWR[1];
    $('#'+id+'-slot').DataTable({
        "paging": false,
        "ordering": false,
        "searching": false,
        "info":     false,
        "columns": [
            {title: "<img class='responsive-img' style='width:100px' src='https://sleepercdn.com/content/nfl/players/"+WR1.sleeper_id+".jpg'/><br>"+WR1.first_name+" "+WR1.last_name+" <i class='red-text'>"+WR1.status+"</i>", data: "metric1", "defaultContent":metricsDefault, className: "dt-center", targets: "_all"}
        ],
        data:[
            {
                "metric1": "Speed-"+WR1.metrics.fortyTime+"<div class='progress'><div class='determinate' style='width:"+WR1.percentiles.fortyTime+"%'></div></div>",
            },
            {
                "metric1": "Jumpball-"+WR1.metrics.jumpball+"<div class='progress'><div class='determinate' style='width:"+WR1.percentiles.jumpball+"%'></div></div>",
            },
            {
                "metric1": "Route Agility-"+WR1.metrics.routeAgility+"<div class='progress'><div class='determinate' style='width:"+WR1.percentiles.routeAgility+"%'></div></div>",
            },
            {
                "metric1": "Bully-"+WR1.metrics.bully+"<div class='progress'><div class='determinate' style='width:"+WR1.percentiles.bully+"%'></div></div>",
            }
        ]
    });

    var WR1 = off.depth_chart.TE[1];
    $('#'+id+'-TE1').DataTable({
        "paging": false,
        "ordering": false,
        "searching": false,
        "info":     false,
        "columns": [
            {title: "<img class='responsive-img' style='width:100px' src='https://sleepercdn.com/content/nfl/players/"+WR1.sleeper_id+".jpg'/><br>"+WR1.first_name+" "+WR1.last_name+" <i class='red-text'>"+WR1.status+"</i>", data: "metric1", "defaultContent":metricsDefault, className: "dt-center", targets: "_all"}
        ],
        data:[
            {
                "metric1": "Speed-"+WR1.metrics.fortyTime+"<div class='progress'><div class='determinate' style='width:"+WR1.percentiles.fortyTime+"%'></div></div>",
            },
            {
                "metric1": "Jumpball-"+WR1.metrics.jumpball+"<div class='progress'><div class='determinate' style='width:"+WR1.percentiles.jumpball+"%'></div></div>",
            },
            {
                "metric1": "Route Agility-"+WR1.metrics.routeAgility+"<div class='progress'><div class='determinate' style='width:"+WR1.percentiles.routeAgility+"%'></div></div>", 
            },
            {
                "metric1": "Block-"+WR1.metrics.runBlock+"<div class='progress'><div class='determinate' style='width:"+WR1.percentiles.runBlock+"%'></div></div>",
            }
        ]
    });

    var CB1 = def.depth_chart.LCB[1];
    $("."+id+"-CB1").DataTable({
        "paging": false,
        "ordering": false,
        "searching": false,
        "info":     false,
        "columns": [
            {title: "<img class='responsive-img' style='width:100px' src='https://sleepercdn.com/content/nfl/players/"+CB1.sleeper_id+".jpg'/><br>"+CB1.first_name+" "+CB1.last_name+" <i class='red-text'>"+CB1.status+"</i>", data: "metric1", "defaultContent":metricsDefault, className: "dt-center", targets: "_all"}
        ],
        data:[
            {
                "metric1": "Speed-"+CB1.metrics.fortyTime+"<div class='progress'><div class='determinate' style='width:"+CB1.percentiles.fortyTime+"%'></div></div>",
            },
            {
                "metric1": "Jumpball-"+CB1.metrics.jumpball+"<div class='progress'><div class='determinate' style='width:"+CB1.percentiles.jumpball+"%'></div></div>",
            },
            {
                "metric1": "Agility-"+CB1.metrics.agility+"<div class='progress'><div class='determinate' style='width:"+CB1.percentiles.agility+"%'></div></div>",
            },
            {
                "metric1": "Bully-"+CB1.metrics.bully+"<div class='progress'><div class='determinate' style='width:"+CB1.percentiles.bully+"%'></div></div>",
            }
        ]
    });

    var CB2 = def.depth_chart.RCB[1];
    $("."+id+"-CB2").DataTable({
        "paging": false,
        "ordering": false,
        "searching": false,
        "info":     false,
        "columns": [
            {title: "<img class='responsive-img' style='width:100px' src='https://sleepercdn.com/content/nfl/players/"+CB2.sleeper_id+".jpg'/><br>"+CB2.first_name+" "+CB2.last_name+" <i class='red-text'>"+CB2.status+"</i>", data: "metric1", "defaultContent":metricsDefault, className: "dt-center", targets: "_all"}
        ],
        data:[
            {
                "metric1": "Speed-"+CB2.metrics.fortyTime+"<div class='progress'><div class='determinate' style='width:"+CB2.percentiles.fortyTime+"%'></div></div>",
            },
            {
                "metric1": "Jumpball-"+CB2.metrics.jumpball+"<div class='progress'><div class='determinate' style='width:"+CB2.percentiles.jumpball+"%'></div></div>",
            },
            {
                "metric1": "Agility-"+CB2.metrics.agility+"<div class='progress'><div class='determinate' style='width:"+CB2.percentiles.agility+"%'></div></div>",
            },
            {
                "metric1": "Bully-"+CB2.metrics.bully+"<div class='progress'><div class='determinate' style='width:"+CB2.percentiles.bully+"%'></div></div>",
            }
        ]
    });

    var CB2 = def.CB.slot;
    $("."+id+"-slot-CB").DataTable({
        "paging": false,
        "ordering": false,
        "searching": false,
        "info":     false,
        "columns": [
            {title: "<img class='responsive-img' style='width:100px' src='https://sleepercdn.com/content/nfl/players/"+CB2.sleeper_id+".jpg'/><br>"+CB2.first_name+" "+CB2.last_name+" <i class='red-text'>"+CB2.status+"</i>", data: "metric1", "defaultContent":metricsDefault, className: "dt-center", targets: "_all"}
        ],
        data:[
            {
                "metric1": "Speed-"+CB2.metrics.fortyTime+"<div class='progress'><div class='determinate' style='width:"+CB2.percentiles.fortyTime+"%'></div></div>",
            },
            {
                "metric1": "Jumpball-"+CB2.metrics.jumpball+"<div class='progress'><div class='determinate' style='width:"+CB2.percentiles.jumpball+"%'></div></div>",
            },
            {
                "metric1": "Agility-"+CB2.metrics.agility+"<div class='progress'><div class='determinate' style='width:"+CB2.percentiles.agility+"%'></div></div>",
            },
            {
                "metric1": "Bully-"+CB2.metrics.bully+"<div class='progress'><div class='determinate' style='width:"+CB2.percentiles.bully+"%'></div></div>",
            }
        ]
    });

    var MLB = def.LBs.middle[0];
    $("."+id+"-MLB").DataTable({
        "paging": false,
        "ordering": false,
        "searching": false,
        "info":     false,
        "columns": [
            {title: "<img class='responsive-img' style='width:100px' src='https://sleepercdn.com/content/nfl/players/"+MLB.sleeper_id+".jpg'/><br>"+MLB.first_name+" "+MLB.last_name+" <i class='red-text'>"+MLB.status+"</i>", data: "metric1", "defaultContent":metricsDefault, className: "dt-center", targets: "_all"}
        ],
        data:[
            {
                "metric1": "Speed-"+MLB.metrics.fortyTime+"<div class='progress'><div class='determinate' style='width:"+MLB.percentiles.fortyTime+"%'></div></div>",
            },
            {
                "metric1": "Jumpball-"+MLB.metrics.jumpball+"<div class='progress'><div class='determinate' style='width:"+MLB.percentiles.jumpball+"%'></div></div>",
            },
            {
                "metric1": "Agility-"+MLB.metrics.agility+"<div class='progress'><div class='determinate' style='width:"+MLB.percentiles.agility+"%'></div></div>",
            },
            {
                "metric1": "Bully-"+MLB.metrics.bully+"<div class='progress'><div class='determinate' style='width:"+MLB.percentiles.bully+"%'></div></div>",
            }
        ]
    });
    
    
};


rlf.initMatchupPage = function() {
    var team1 = rlfData.team1;
    var team2 = rlfData.team2;

    rlf.initMatchup(team1, team2, "team1");
    rlf.initMatchup(team2, team1, "team2");

    $('.determinate').each(function(){
        var width = $(this).width()/$(this).parent().width();
        if (width > .69) {
            $(this).css("background-color", "green");
        }

        if (width < .69 && width > .40) {
            $(this).css("background-color", "yellow");
        }

        if (width < .40) {
            $(this).css("background-color", "red");
        }
    });

    $(".tab a").css("font-size", "12px");

    $("img").on("error", function () {
        $(this).unbind("error").attr("src", "/images/placeholder.jpeg").css("height", "60px");
    });


};
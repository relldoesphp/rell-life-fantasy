//store all functions in object

var rlf =  {
    /*************************** Compare Player ***************/
    initComparePage : function(){
        var position = rlfData.players[0].position;
        rlf.initCompareMesChart(position);
        rlf.initCompareSearches(position);
        rlf.initCompareProspect(position);
        rlf.initCompareSkillset(position);
        rlf.initCompareTables(position);
    },

    initCompareSearches : function(){
        var compareList = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('full_name'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: {
                url: '/player/query/%QUERY',
                wildcard: '%QUERY'
            },
            dupDetector: function(remoteMatch, localMatch) {
                return remoteMatch.id === localMatch.id;
            }
        });

        $('#compare-1').typeahead({
                    hint: true,
                    highlight: true,
                    minLength: 3
                },
                {
                    name: 'compare-1',
                    source: compareList,
                    display: 'full_name',
                });


        $('#compare-2').typeahead({
                    hint: true,
                    highlight: true,
                    minLength: 3
                },
                {
                    name: 'compare-2',
                    source: compareList,
                    display: 'full_name',
                });


        $('.compare-search.typeahead').on('typeahead:selected', function(evt, item){
            $(this).typeahead('val', item.nohash);
            var id = $(this).attr("id");
            if (id === "compare-1") {
                $("#compare-button").attr("data-player1", item.id);
            } else {
                $("#compare-button").attr("data-player2", item.id);
            }
            var position = item.position;
            compareList.clear();
            compareList.local = rlfData.lists[position];
            delete compareList.remote;
            compareList.initialize(true);
        });

        $('#compare-button').on("click", function(){
            var postData = {
                player1: $(this).data("player1"),
                player2: $(this).data("player2")
            };
            window.location = "/player/compare?player1="+postData['player1']+"&player2="+postData['player2'];
        });
    },

    initCompareMesChart : function(){
        var percent1 = rlfData.players[0].percentiles;
        var percent2 = rlfData.players[1].percentiles;

        var data = [
            {
                type: 'scatterpolar',
                r: [percent1.height, percent1.weight, percent1.armsInches, percent1.bmi, percent1.fortyTime, percent1.benchPress, percent1.verticalJump, percent1.broadJump, percent1.cone, percent1.shuttle],
                theta: ['height', 'weight', 'arms', 'bmi', '40', 'bench', 'vertical', 'broad', '3cone', 'shuttle'],
                fill: 'toself',
                name: rlfData.players[0].first_name+' '+rlfData.players[0].last_name,
                opacity: 0.5,
                marker: {
                    color: 'rgba(29, 233, 195, 0.4)'
                }
            },
            {
                type: 'scatterpolar',
                r: [percent2.height, percent2.weight, percent2.armsInches, percent2.bmi, percent2.fortyTime, percent2.benchPress, percent2.verticalJump, percent2.broadJump, percent2.cone, percent2.shuttle],
                theta: ['height', 'weight', 'arms', 'bmi', '40', 'bench', 'vertical', 'broad', '3cone', 'shuttle'],
                fill: 'toself',
                name: rlfData.players[1].first_name+' '+rlfData.players[1].last_name,
                opacity: 0.5,
                marker: {
                    color: 'rgba(174, 3, 230, 0.4)'
                }
            },

            ];

        var layout = {
            polar: {
                radialaxis: {
                    visible: true,
                    range: [0, 100]
                }
            },
            font: {size: 10},
            autosize: false,
            width: 400,
            height: 300,
            margin: {
                l: 0,
                r: 25,
                b: 20,
                t: 20,
                pad: 0
            },
            showlegend: true,
            legend: {
                margin: {
                    t: 25
                },
                "orientation": "h",
                    x: .1,
                    y: -.10,
            }
        };

        Plotly.plot("radar-graph", data, layout, {responsive: true, displayModeBar: false, staticPlot: true});
        $("#radar-graph").addClass("scale-in");
    },

    initCompareProspect : function(position){

        var percent1 = rlfData.players[0].percentiles;
        var metrics1 = rlfData.players[0].metrics;
        var ordinals1 = rlfData.players[0].ordinals;
        var percent2 = rlfData.players[1].percentiles;
        var metrics2 = rlfData.players[1].metrics;
        var ordinals2 = rlfData.players[1].ordinals;

        if (position == "WR") {
            var chartData = {
                labels: ['College Score','Bully Score', 'Speed', 'Route Agility', 'Jumpball', 'Elusiveness', 'Run Power'],
                datasets: [
                    {
                        type: 'bar',
                        backgroundColor: 'rgb(29, 233, 195, 0.4)',
                        label: rlfData.players[0].first_name+" "+rlfData.players[0].last_name,
                        borderWidth: 2,
                        fill: false,
                        data: [percent1.collegeScore, percent1.bully, percent1.fortyTime, percent1.routeAgility, percent1.jumpball, percent1.elusiveness, percent1.power],
                        ordinals: [ordinals1.collegeScore, ordinals1.bully, ordinals1.fortyTime, ordinals1.routeAgility, ordinals1.jumpball, ordinals1.elusiveness, ordinals1.power ],
                        metrics: [metrics1.collegeScore, metrics1.bully, metrics1.fortyTime, metrics1.routeAgility, metrics1.jumpball, metrics1.elusiveness, metrics1.power]
                    },
                    {
                        type: 'bar',
                        backgroundColor: 'rgba(174, 3, 230, 0.4)',
                        label: rlfData.players[1].first_name+" "+rlfData.players[1].last_name,
                        borderWidth: 2,
                        fill: false,
                        data: [percent2.collegeScore, percent2.bully, percent2.fortyTime, percent2.routeAgility, percent2.jumpball, percent2.elusiveness, percent2.power],
                        ordinals: [ordinals2.collegeScore, ordinals2.bully, ordinals2.fortyTime, ordinals2.routeAgility, ordinals2.jumpball, ordinals2.elusiveness, ordinals2.power ],
                        metrics: [metrics2.collegeScore, metrics2.bully, metrics2.fortyTime, metrics2.routeAgility, metrics2.jumpball, metrics2.elusiveness, metrics2.power]
                    }
                ]
            };
            rlf.makeProspectChart(chartData);
        }

        if (position == "RB") {
            var chartData = {
                labels: ['Speed', 'Juke Agility', 'Route Agility', 'Elusiveness', 'Run Power', 'Speed Score'],
                datasets: [
                    {
                        type: 'bar',
                        backgroundColor: 'rgb(29, 233, 195, 0.4)',
                        label: rlfData.players[0].first_name+" "+rlfData.players[0].last_name,
                        borderWidth: 2,
                        fill: false,
                        data: [percent1.fortyTime, percent1.jukeAgility, percent1.routeAgility, percent1.elusiveness, percent1.power, percent1.speedScore],
                        ordinals: [ordinals1.fortyTime, ordinals1.jukeAgility, ordinals1.routeAgility, ordinals1.elusiveness, ordinals1.power, ordinals1.speedScore],
                        metrics: [metrics1.fortyTime, metrics1.jukeAgility, metrics1.routeAgility, metrics1.elusiveness, metrics1.power, metrics1.speedScore]
                    },
                    {
                        type: 'bar',
                        backgroundColor: 'rgba(174, 3, 230, 0.4)',
                        label: rlfData.players[1].first_name+" "+rlfData.players[1].last_name,
                        borderWidth: 2,
                        fill: false,
                        data: [percent2.fortyTime, percent2.jukeAgility, percent2.routeAgility, percent2.elusiveness, percent2.power, percent2.speedScore],
                        ordinals: [ordinals2.fortyTime, ordinals2.jukeAgility, ordinals2.routeAgility, ordinals2.elusiveness, ordinals2.power, ordinals2.speedScore],
                        metrics: [metrics2.fortyTime, metrics2.jukeAgility, metrics2.routeAgility, metrics2.elusiveness, metrics2.power, metrics2.speedScore]
                    }
                ]
            };
            rlf.makeProspectChart(chartData);
        }

        if (position == "TE") {
            var chartData = {
                labels: ['Speed', 'Route Agility', 'Jumpball', 'Elusiveness', 'Run Power', 'Bully Score', 'Run Block'],
                datasets: [
                    {
                        type: 'bar',
                        backgroundColor: 'rgb(29, 233, 195, 0.4)',
                        label: 'WR Skills',
                        borderWidth: 2,
                        fill: false,
                        data: [percent1.fortyTime, percent1.routeAgility, percent1.jumpball, percent1.elusiveness, percent1.power, percent1.bully, percent1.runBlock],
                        ordinals: [ordinals1.fortyTime, ordinals1.routeAgility, ordinals1.jumpball, ordinals1.elusiveness, ordinals1.power, ordinals1.bully, ordinals1.runBlock ],
                        metrics: [metrics1.fortyTime, metrics1.routeAgility, metrics1.jumpball, metrics1.elusiveness, metrics1.power, metrics1.bully, metrics1.runBlock]
                    },
                    {
                        type: 'bar',
                        backgroundColor: 'rgba(174, 3, 230, 0.4)',
                        label: 'WR Skills',
                        borderWidth: 2,
                        fill: false,
                        data: [percent2.fortyTime, percent2.routeAgility, percent2.jumpball, percent2.elusiveness, percent2.power, percent2.bully, percent2.runBlock],
                        ordinals: [ordinals2.fortyTime, ordinals2.routeAgility, ordinals2.jumpball, ordinals2.elusiveness, ordinals2.power, ordinals2.bully, ordinals2.runBlock ],
                        metrics: [metrics2.fortyTime, metrics2.routeAgility, metrics2.jumpball, metrics2.elusiveness, metrics2.power, metrics2.bully, metrics2.runBlock]
                    }
                ]
            };
            rlf.makeProspectChart(chartData);
        }

    },

    initCompareSkillset : function(position){
        if (position == "WR") {

            $(".player-skillz-role1").text("Slot:");
            $(".player-skillz-role2").text("Deep Threat:");
            $(".player-skillz-role3").text("Outside X:");

            var slotpercent = Math.round(rlfData.players[0].percentiles.slot);
            var deeppercent = Math.round(rlfData.players[0].percentiles.deep);
            var alphapercent = Math.round(rlfData.players[0].percentiles.alpha);

            $("#player1-skill .role-one-bar .determinate").css("width", slotpercent + "%");
            $("#player1-skill .role-one-score").text(rlfData.players[0].metrics['slot']);


            $("#player1-skill .role-two-bar .determinate").css("width", deeppercent + "%");
            $("#player1-skill .role-two-score").text(rlfData.players[0].metrics['deep']);


            $("#player1-skill .role-three-bar .determinate").css("width", alphapercent + "%");
            $("#player1-skill .role-three-score").text(rlfData.players[0].metrics['alpha']);

            /*** Player2 ***/
            var slotpercent = Math.round(rlfData.players[1].percentiles.slot);
            var deeppercent = Math.round(rlfData.players[1].percentiles.deep);
            var alphapercent = Math.round(rlfData.players[1].metrics.alpha);

            $("#player2-skill .role-one-bar .determinate").css("width", slotpercent + "%");
            $("#player2-skill .role-one-score").text(rlfData.players[1].metrics['slot']);

            $("#player2-skill .role-two-bar .determinate").css("width", deeppercent + "%");
            $("#player2-skill .role-two-score").text(rlfData.players[1].metrics['deep']);

            $("#player2-skill .role-three-bar .determinate").css("width", alphapercent + "%");
            $("#player2-skill .role-three-score").text(rlfData.players[1].metrics['alpha']);
        }

        if (position == "RB") {
            var grinderpercent1 = Math.round(rlfData.players[0].metrics.grinder);
            var passCatcherpercent1 = Math.round(rlfData.players[0].metrics.passCatcher);
            var alphapercent1 = Math.round(rlfData.players[0].metrics.alpha);

            $(".player-skillz-role1").text("Grinder:");
            $(".player-skillz-role2").text("Receiver:");
            $(".player-skillz-role3").text("3 Down:");

            $("#player1-skill .role-one-bar .determinate").css("width", grinderpercent1 + "%");
            $("#player1-skill .role-one-score").text(grinderpercent1 + "%")

            $("#player1-skill .role-two-bar .determinate").css("width", passCatcherpercent1 + "%");
            $("#player1-skill .role-two-score").text(passCatcherpercent1 + "%")

            $("#player1-skill .role-three-bar .determinate").css("width", alphapercent1 + "%");
            $("#player1-skill .role-three-score").text(alphapercent1 + "%")

            /*** Player2 ***/
            var grinderpercent2 = Math.round(rlfData.players[1].metrics.grinder);
            var passCatcherpercent2 = Math.round(rlfData.players[1].metrics.passCatcher);
            var alphapercent2 = Math.round(rlfData.players[1].metrics.alpha);

            $("#player2-skill .role-one-bar .determinate").css("width", grinderpercent2 + "%");
            $("#player2-skill .role-one-score").text(grinderpercent2 + "%")

            $("#player2-skill .role-two-bar .determinate").css("width", passCatcherpercent2 + "%");
            $("#player2-skill .role-two-score").text(passCatcherpercent2 + "%")

            $("#player2-skill .role-three-bar .determinate").css("width", alphapercent2 + "%");
            $("#player2-skill .role-three-score").text(alphapercent2 + "%")
        }

        if (position == "TE") {
            var move1 = Math.round(rlfData.players[0].metrics.move)
            var inline1 = Math.round(rlfData.players[0].metrics.inLine);
            var alpha1 = Math.round(rlfData.players[0].metrics.alpha);

            $(".player-skillz-role1").text("Move:");
            $(".player-skillz-role2").text("In Line:");
            $(".player-skillz-role3").text("2 Way TE:");

            $("#player1-skill .role-one-bar .determinate").css("width", move1 + "%");
            $("#player1-skill .role-one-score").text(move1 + "%")

            $("#player1-skill .role-two-bar .determinate").css("width", inline1 + "%");
            $("#player1-skill .role-two-score").text(inline1 + "%")

            $("#player1-skill .role-three-bar .determinate").css("width", alpha1 + "%");
            $("#player1-skill .role-three-score").text(alpha1 + "%")

            /*** Player2 ***/
            var move2 = Math.round(rlfData.players[1].metrics.move)
            var inline2 = Math.round(rlfData.players[1].metrics.inLine);
            var alpha2 = Math.round(rlfData.players[1].metrics.alpha);

            $("#player2-skill .role-one-bar .determinate").css("width", move2 + "%");
            $("#player2-skill .role-one-score").text(move2 + "%")

            $("#player2-skill .role-two-bar .determinate").css("width", inline2 + "%");
            $("#player2-skill .role-two-score").text(inline2 + "%")

            $("#player2-skill .role-three-bar .determinate").css("width", alpha2 + "%");
            $("#player2-skill .role-three-score").text(alpha2 + "%")
        }
    },

    initCompareTables : function(position){
        var player1 = rlfData.players[0];
        var player2 = rlfData.players[1];

        if (position == "WR" || position == "TE") {
            $('#season-stats').DataTable({
                "paging": false,
                "ordering": false,
                "searching": false,
                "info":false,
                "columns": [
                    {title: "Year", data: "name", "defaultContent":0},
                    {title: "GP", data: "stats.gp", "defaultContent":0},
                    {title: "PPG", data: "ppg", "defaultContent":0},
                    {title: "Recs", data: "stats.rec", "defaultContent":0},
                    {title: "Yds", data: "stats.rec_yd", "defaultContent":0},
                    {title: "Tds", data: "stats.rec_td", "defaultContent":0},
                    {title: "Tgts", data: "stats.rec_tgt", "defaultContent":0},
                    {title: "YPR", data: "stats.rec_ypr", "defaultContent":0},
                    {title: "YPT", data: "stats.rec_ypt", "defaultContent":0},
                    {title: "Deep Yds", data: "stats.rec_ypt", "defaultContent":0}
                ],
                data:[
                    {
                        "name":player1.first_name+" "+player1.last_name,
                        "stats":player1.seasonTable[0].stats,
                        "ppg":(player1.seasonTable[0].stats.pts_ppr/player1.seasonTable[0].stats.gp).toFixed(1),
                    },
                    {
                        "name":player2.first_name+" "+player2.last_name,
                        "stats":player2.seasonTable[0].stats,
                        "ppg":(player2.seasonTable[0].stats.pts_ppr/player2.seasonTable[0].stats.gp).toFixed(1),
                    }
                ]
            });

            var metricsDefault = "Not Available<div class='progress'><div class='determinate' style='width:0%'></div></div>";

            $('#metrics').DataTable({
                "paging": false,
                "ordering": false,
                "searching": false,
                "info":false,
                "columns": [
                    {title: "", data: "name", "defaultContent":0},
                    {title: player1.first_name+" "+player1.last_name, data: "metric1", "defaultContent":metricsDefault, className: "dt-center", targets: "_all"},
                    {title: player2.first_name+" "+player2.last_name, data: "metric2", "defaultContent":metricsDefault, className: "dt-center", targets: "_all"},
                ],
                data:[
                    {
                        "name": "40 time",
                        "metric1": player1.metrics.fortyTime+"<div class='progress'><div class='determinate' style='width:"+player1.percentiles.fortyTime+"%'></div></div>",
                        "metric2": player2.metrics.fortyTime+"<div class='progress'><div class='determinate' style='width:"+player2.percentiles.fortyTime+"%'></div></div>",
                    },
                    {
                        "name": "3 cone",
                        "metric1": player1.metrics.cone+"<div class='progress'><div class='determinate' style='width:"+player1.percentiles.cone+"%'></div></div>",
                        "metric2": player2.metrics.cone+"<div class='progress'><div class='determinate' style='width:"+player2.percentiles.cone+"%'></div></div>",
                    },
                    {
                        "name": "Shuttle",
                        "metric1": player1.metrics.shuttle+"<div class='progress'><div class='determinate' style='width:"+player1.percentiles.shuttle+"%'></div></div>",
                        "metric2": player2.metrics.shuttle+"<div class='progress'><div class='determinate' style='width:"+player2.percentiles.shuttle+"%'></div></div>",
                    },
                    {
                        "name": "Vertical Jump",
                        "metric1": player1.metrics.verticalJump+"<div class='progress'><div class='determinate' style='width:"+player1.percentiles.verticalJump+"%'></div></div>",
                        "metric2": player2.metrics.verticalJump+"<div class='progress'><div class='determinate' style='width:"+player2.percentiles.verticalJump+"%'></div></div>",
                    },
                    {
                        "name": "Broad Jump",
                        "metric1": player1.metrics.broadJump+"<div class='progress'><div class='determinate' style='width:"+player1.percentiles.broadJump+"%'></div></div>",
                        "metric2": player2.metrics.broadJump+"<div class='progress'><div class='determinate' style='width:"+player2.percentiles.broadJump+"%'></div></div>",
                    },
                    {
                        "name": "Bench",
                        "metric1": player1.metrics.benchPress+"<div class='progress'><div class='determinate' style='width:"+player1.percentiles.benchPress+"%'></div></div>",
                        "metric2": player2.metrics.benchPress+"<div class='progress'><div class='determinate' style='width:"+player2.percentiles.benchPress+"%'></div></div>",
                    }
                ]
            });

            $("#college-table").DataTable({
                "paging": false,
                "ordering": false,
                "searching": false,
                "info":false,
                "columns": [
                    {title: "", data: "name", "defaultContent":0},
                    {title: player1.first_name+" "+player1.last_name, data: "metric1", "defaultContent":0, className: "dt-center", targets: "_all"},
                    {title: player2.first_name+" "+player2.last_name, data: "metric2", "defaultContent":0, className: "dt-center", targets: "_all"},
                ],
                "data":[
                    {
                        "name": "College",
                        "metric1": player1.player_info.college,
                        "metric2": player2.player_info.college
                    },
                    {
                        "name": "Seasons",
                        "metric1": player1.metrics.collegeSeasons,
                        "metric2": player2.metrics.collegeSeasons
                    },
                    {
                        "name": "Breakout Class",
                        "metric1": player1.metrics.breakoutClass+"<div class='progress'><div class='determinate' style='width:"+player1.percentiles.breakoutClass+"%'></div></div>",
                        "metric2": player2.metrics.breakoutClass+"<div class='progress'><div class='determinate' style='width:"+player2.percentiles.breakoutClass+"%'></div></div>",
                    },
                    {
                        "name": "Breakout Years",
                        "metric1": player1.metrics.breakoutSeasons+"<div class='progress'><div class='determinate' style='width:"+player1.percentiles.breakoutYears+"%'></div></div>",
                        "metric2": player2.metrics.breakoutSeasons+"<div class='progress'><div class='determinate' style='width:"+player2.percentiles.breakoutYears+"%'></div></div>",
                    },
                    {
                        "name": "Best Dominator",
                        "metric1": player1.metrics.bestDominator+"<div class='progress'><div class='determinate' style='width:"+player1.percentiles.breakoutDominator+"%'></div></div>",
                        "metric2": player2.metrics.bestDominator+"<div class='progress'><div class='determinate' style='width:"+player2.percentiles.breakoutDominator+"%'></div></div>",
                    },
                    {
                        "name": "College Score",
                        "metric1": player1.metrics.collegeScore+"<div class='progress'><div class='determinate' style='width:"+player1.percentiles.collegeScore+"%'></div></div>",
                        "metric2": player2.metrics.collegeScore+"<div class='progress'><div class='determinate' style='width:"+player2.percentiles.collegeScore+"%'></div></div>",
                    }
                ]
            });
        }

        if (position == "RB") {
            $('#season-stats').DataTable({
                "paging": false,
                "ordering": false,
                "searching": false,
                "info":false,
                "columns": [
                    {title: "Year", data: "name", "defaultContent":0},
                    {title: "GP", data: "stats.gp", "defaultContent":0},
                    {title: "PPG", data: "ppg", "defaultContent":0},
                    {title: "Rush Yds", data: "stats.rush_yd", "defaultContent":0},
                    {title: "Rush Tds", data: "stats.rush_td", "defaultContent":0},
                    {title: "Rush Atts", data: "stats.rush_att", "defaultContent":0},
                    {title: "Recs", data: "stats.rec", "defaultContent":0},
                    {title: "Tgts", data: "stats.rec_tgt", "defaultContent":0},
                    {title: "Rec Yds", data: "stats.rec_yd", "defaultContent":0},
                    {title: "Rec Tds", data: "stats.rec_td", "defaultContent":0}
                ],
                data:[
                    {
                        "name":player1.first_name+" "+player1.last_name,
                        "stats":player1.seasonTable[0].stats,
                        "ppg":(player1.seasonTable[0].stats.pts_ppr/player1.seasonTable[0].stats.gp).toFixed(1),
                    },
                    {
                        "name":player2.first_name+" "+player2.last_name,
                        "stats":player2.seasonTable[0].stats,
                        "ppg":(player2.seasonTable[0].stats.pts_ppr/player2.seasonTable[0].stats.gp).toFixed(1),
                    }
                ]
            });

            var metricsDefault = "Not Available<div class='progress'><div class='determinate' style='width:0%'></div></div>";

            $('#metrics').DataTable({
                "paging": false,
                "ordering": false,
                "searching": false,
                "info":false,
                "columns": [
                    {title: "", data: "name", "defaultContent":0},
                    {title: player1.first_name+" "+player1.last_name, data: "metric1", "defaultContent":metricsDefault, className: "dt-center", targets: "_all"},
                    {title: player2.first_name+" "+player2.last_name, data: "metric2", "defaultContent":metricsDefault, className: "dt-center", targets: "_all"},
                ],
                data:[
                    {
                        "name": "40 time",
                        "metric1": player1.metrics.fortyTime+"<div class='progress'><div class='determinate' style='width:"+player1.percentiles.fortyTime+"%'></div></div>",
                        "metric2": player2.metrics.fortyTime+"<div class='progress'><div class='determinate' style='width:"+player2.percentiles.fortyTime+"%'></div></div>",
                    },
                    {
                        "name": "3 cone",
                        "metric1": player1.metrics.cone+"<div class='progress'><div class='determinate' style='width:"+player1.percentiles.cone+"%'></div></div>",
                        "metric2": player2.metrics.cone+"<div class='progress'><div class='determinate' style='width:"+player2.percentiles.cone+"%'></div></div>",
                    },
                    {
                        "name": "Shuttle",
                        "metric1": player1.metrics.shuttle+"<div class='progress'><div class='determinate' style='width:"+player1.percentiles.shuttle+"%'></div></div>",
                        "metric2": player2.metrics.shuttle+"<div class='progress'><div class='determinate' style='width:"+player2.percentiles.shuttle+"%'></div></div>",
                    },
                    {
                        "name": "Vertical Jump",
                        "metric1": player1.metrics.verticalJump+"<div class='progress'><div class='determinate' style='width:"+player1.percentiles.verticalJump+"%'></div></div>",
                        "metric2": player2.metrics.verticalJump+"<div class='progress'><div class='determinate' style='width:"+player2.percentiles.verticalJump+"%'></div></div>",
                    },
                    {
                        "name": "Broad Jump",
                        "metric1": player1.metrics.broadJump+"<div class='progress'><div class='determinate' style='width:"+player1.percentiles.broadJump+"%'></div></div>",
                        "metric2": player2.metrics.broadJump+"<div class='progress'><div class='determinate' style='width:"+player2.percentiles.broadJump+"%'></div></div>",
                    },
                    {
                        "name": "Bench",
                        "metric1": player1.metrics.benchPress+"<div class='progress'><div class='determinate' style='width:"+player1.percentiles.benchPress+"%'></div></div>",
                        "metric2": player2.metrics.benchPress+"<div class='progress'><div class='determinate' style='width:"+player2.percentiles.benchPress+"%'></div></div>",
                    }
                ]
            });

            $("#college-table").DataTable({
                "paging": false,
                "ordering": false,
                "searching": false,
                "info":false,
                "columns": [
                    {title: "", data: "name", "defaultContent":0},
                    {title: player1.first_name+" "+player1.last_name, data: "metric1", "defaultContent":0, className: "dt-center", targets: "_all"},
                    {title: player2.first_name+" "+player2.last_name, data: "metric2", "defaultContent":0, className: "dt-center", targets: "_all"},
                ],
                "data":[
                    {
                        "name": "College",
                        "metric1": player1.player_info.college,
                        "metric2": player2.player_info.college
                    },
                    {
                        "name": "Seasons",
                        "metric1": player1.metrics.collegeSeasons,
                        "metric2": player2.metrics.collegeSeasons
                    },
                    {
                        "name": "Breakout Class",
                        "metric1": player1.metrics.breakoutClass+"<div class='progress'><div class='determinate' style='width:"+player1.percentiles.breakoutClass+"%'></div></div>",
                        "metric2": player2.metrics.breakoutClass+"<div class='progress'><div class='determinate' style='width:"+player2.percentiles.breakoutClass+"%'></div></div>",
                    },
                    {
                        "name": "Breakout Seasons",
                        "metric1": player1.metrics.breakoutSeasons+"<div class='progress'><div class='determinate' style='width:"+player1.percentiles.breakoutSeasons+"%'></div></div>",
                        "metric2": player2.metrics.breakoutSeasons+"<div class='progress'><div class='determinate' style='width:"+player2.percentiles.breakoutSeasons+"%'></div></div>",
                    },
                    {
                        "name": "Best Dominator",
                        "metric1": player1.metrics.bestDominator+"<div class='progress'><div class='determinate' style='width:"+player1.percentiles.bestDominator+"%'></div></div>",
                        "metric2": player2.metrics.bestDominator+"<div class='progress'><div class='determinate' style='width:"+player2.percentiles.bestDominator+"%'></div></div>",
                    },
                    {
                        "name": "Best Reception Share",
                        "metric1": player1.metrics.bestRecDominator+"<div class='progress'><div class='determinate' style='width:"+player1.percentiles.bestRecDominator+"%'></div></div>",
                        "metric2": player2.metrics.bestRecDominator+"<div class='progress'><div class='determinate' style='width:"+player2.percentiles.bestRecDominator+"%'></div></div>",
                    },
                    {
                        "name": "College Score",
                        "metric1": player1.metrics.collegeScore+"<div class='progress'><div class='determinate' style='width:"+player1.percentiles.collegeScore+"%'></div></div>",
                        "metric2": player2.metrics.collegeScore+"<div class='progress'><div class='determinate' style='width:"+player2.percentiles.collegeScore+"%'></div></div>",
                    }
                ]
            });
        }

        rlf.colorDeterminates();
    },

    /************************* QB Metrics **************************/
    initQbPage : function() {
        rlf.initProsChartsQB();
        rlf.initMesChartsQb();

        var roleFits = [
            {
                "name":"Arm Talent",
                "value": rlfData.player.metrics.armTalent.toFixed(2),
                "percentile":rlfData.player.ordinals.armTalent,
                "percent":rlfData.player.percentiles.armTalent
            },
            {
                "name":"Mobility",
                "value": rlfData.player.metrics.mobility.toFixed(2),
                "percentile":rlfData.player.ordinals.mobility,
                "percent":rlfData.player.percentiles.mobility

            },
            {
                "name":"PlayMaker",
                "value": rlfData.player.metrics.playmaker.toFixed(2),
                "percentile":rlfData.player.ordinals.playmaker,
                "percent":rlfData.player.percentiles.playmaker
            }
        ];

        rlf.makeRoleFits(roleFits);

        var seasonColumns = [
            {title: "Year", searchable: true, targets: 0, data: "year", "defaultContent":0},
            {title: "GP", data: "stats.gp", "defaultContent":0},
            {title: "Pts", data: "stats.pts_ppr", "defaultContent":0},
            {title: "PPG", data: "stats.pts_ppr_avg", "defaultContent":0},
            {title: "Rank", data: "ranks.pts_ppr_avg", "defaultContent":0},
            {title: "Pass Yds", data: "stats.pass_yd", "defaultContent":0},
            {title: "Pass Tds", data: "stats.pass_td", "defaultContent":0},
            {title: "Pass Cmp", data: "stats.pass_cmp", "defaultContent":0},
            {title: "Pass Atts", data: "stats.pass_att", "defaultContent":0},
            {title: "Pass Rating", data: "stats.pass_rtg_avg", "defaultContent":0},
            {title: "Int", data: "stats.pass_int", "defaultContent":0},
            {title: "Sacks", data: "stats.pass_sack", "defaultContent":0},
            {title: "Rush Atts", data: "stats.rush_att", "defaultContent":0},
            {title: "Rush Yds", data: "stats.rush_yd", "defaultContent":0},
            {title: "Rush Tds", data: "stats.rush_td", "defaultContent":0},
            {title: "Pass FDs", data: "stats.pass_fd", "defaultContent":0},
            {title: "Rush FDs", data: "stats.rush_fd", "defaultContent":0}
        ];
        $('#season-stats').append("<tfoot><th colspan=\"2\">Career Average:<br>Career Total:</th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th></tfoot>");

        rlf.makeSeasonTable(seasonColumns);

        var gameLogColumns = [
            {title: "Year", searchable: true, data: "year"},
            {title: "Wk", data: "week"},
            {title: "Pts",  data: "stats.pts_ppr", "defaultContent":0},
            {title: "Pass Yds", data: "stats.pass_yd", "defaultContent":0},
            {title: "Pass Tds", data: "stats.pass_td", "defaultContent":0},
            {title: "Completions", data: "stats.pass_cmp", "defaultContent":0},
            {title: "Pass Atts", data: "stats.pass_att", "defaultContent":0},
            {title: "Rush Yds", data: "stats.rush_yd", "defaultContent":0},
            {title: "Rush Tds", data: "stats.rush_td", "defaultContent":0},
            {title: "Rush Atts", data: "stats.rush_att", "defaultContent":0},
        ];

        rlf.makeGameLogTable(gameLogColumns);

        var collegeColumns = [
            { title: "Year", data: "year", "defaultContent":0},
            { title: "College", data: "stats.college", "defaultContent": "n/a"},
            { title: "Class", data: "stats.class", "defaultContent": "n/a" },
            { title: "GP", data: "stats.games", "defaultContent": 0 },
            { title: "Cmp", data: "stats.cmp", "defaultContent": 0 },
            { title: "Att", data: "stats.att", "defaultContent": 0},
            { title: "Pct", data: "stats.pct", "defaultContent": 0},
            { title: "Yds", data: "stats.yds", "defaultContent": 0},
            { title: "YPA", data: "stats.ypa", "defaultContent": 0},
            { title: "Tds", data: "stats.tds", "defaultContent": 0},
            { title: "Ints", data: "stats.ints", "defaultContent": 0},
            { title: "Rush Atts", data: "stats.rushAtt", "defaultContent": 0},
            { title: "Rush Yds", data: "stats.rushYds", "defaultContent": 0},
            { title: "Rush Tds", data: "stats.rushTds", "defaultContent": 0}
        ];

        rlf.makeCollegeTable(collegeColumns);
    },

    initProsChartsQB : function(){
        var percent = rlfData.player.percentiles;
        var metrics = rlfData.player.metrics;
        var ordinals = rlfData.player.ordinals;
        var chartData = {
            labels: ['Speed', 'Agility', 'Run Power', 'Elusiveness', 'Throw Power', 'Accuracy', 'Wonderlic'],
            datasets: [{
                type: 'bar',
                stack: 'Stack One',
                backgroundColor: 'rgb(29, 233, 195, 0.4)',
                label: 'QB Skills',
                borderWidth: 2,
                fill: false,
                data: [percent.fortyTime, percent.agility, percent.power, percent.elusiveness, percent.throwVelocity, percent.depthAdjPct, percent.wonderlic],
                ordinals: [ordinals.fortyTime, ordinals.agility, ordinals.power, ordinals.elusiveness, ordinals.throwVelocity, ordinals.depthAdjPct, ordinals.wonderlic],
                metrics: [metrics.fortyTime, metrics.agility, metrics.power, metrics.elusiveness, metrics.throwVelocity, metrics.depthAdjPct, metrics.wonderlic]
            }]
        };
        rlf.makeProspectChart(chartData);
    },

    initMesChartsQb : function() {
        var percent = rlfData.player.percentiles;
        var info = [percent.heightInches, percent.weight, percent.armsInches, percent.bmi, percent.fortyTime, percent.verticalJump, percent.broadJump, percent.cone, percent.shuttle];
        var labels = ['height', 'weight', 'arms', 'bmi', '40', 'vertical', 'broad', '3cone', 'shuttle'];
        rlf.makeRadarGraph(info,labels);
    },
    /****************************************** RB stuff **************************************************************/

    initRbPage : function(){
        //rlf.initOppChartsRB();
        rlf.initMesChartsRB();
        rlf.initProsChartsRB();

        if (rlfData.player.college_stats !== undefined) {
            var collegeColumns = [
                { title: "Year", data: "year", "defaultContent":0},
                { title: "College", data: "college", "defaultContent": "n/a"},
                { title: "Class", data: "class", "defaultContent": "n/a" },
                { title: "GP", data: "games", "defaultContent": 0 },
                { title: "Carries", data: "rushAtt", "defaultContent": 0 },
                { title: "Rush Yds", data: "rushYds", "defaultContent": 0},
                { title: "YPC", data: "rushAvg", "defaultContent": 0},
                { title: "Rush Tds", data: "rushTds", "defaultContent": 0},
                { title: "Recs", data: "recs", "defaultContent": 0},
                { title: "Rec Yds", data: "recYds", "defaultContent": 0},
                { title: "Rec Tds", data: "recTds", "defaultContent": 0},
                { title: "% of Carries", data: "carryDom", "defaultContent": 0},
                { title: "% of Recs", data: "recDom", "defaultContent": 0},
                { title: "% of total yds", data: "ydDom", "defaultContent": 0},
                { title: "% of total tds", data: "tdDom", "defaultContent": 0},
            ];

            rlf.makeCollegeTable(collegeColumns);


            if (rlfData.player.metrics.collegeScore !== null) {
                $(".college-row-one p").text("Full Breakout Class: " + rlfData.player.metrics.breakoutClass);
                $(".college-row-two p").text("Dominate Seasons: " + rlfData.player.metrics.breakoutSeasons + " out of " + rlfData.player.metrics.collegeSeasons);
                $(".college-row-three p").text("Best Dominator: " + rlfData.player.metrics.bestDominator + "%");
                $(".college-row-four p").text("Best Reception Share: " + rlfData.player.metrics.bestRecDominator + "%");
                $(".donut-inner h5").text(rlfData.player.metrics.collegeScore);
                $(".donut-inner span").text(rlfData.player.ordinals.collegeScore + " percentile");
                var config = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [rlfData.player.percentiles.collegeScore, Math.round(100 - rlfData.player.percentiles.collegeScore, 2)],
                            backgroundColor: ['rgba(174, 3, 230, 0.25)', 'white'],
                            label: 'College Score'
                        }],
                        labels: [
                            'College Score',
                            ''
                        ]
                    },
                    options: {
                        cutoutPercentage: 75,
                        legend: {
                            position: 'top'
                        },
                        title: {
                            display: false,
                            text: 'College Score'
                        },
                        responsive: true,
                        animation: {
                            animateScale: true,
                            animateRotate: true
                        }
                    }
                };

                var ctx = document.getElementById('college-doughnut').getContext('2d');
                var myDoughut = new Chart(ctx, config);
            }

        }



        var grinderpercent =  rlfData.player.metrics.grinder;
        var passCatcherpercent = rlfData.player.metrics.passCatcher;
        var alphapercent = rlfData.player.metrics.alpha;



        var roleFits = [
            {
                "name":"Grinder Score",
                "value":grinderpercent,
                "percentile":rlfData.player.ordinals.grinder,
                "percent":rlfData.player.percentiles.grinder
            },
            {
                "name":"Receiver Score",
                "value":passCatcherpercent,
                "percentile":rlfData.player.ordinals.passCatcher,
                "percent":rlfData.player.percentiles.passCatcher
            },
            {
                "name":"3 Down Back",
                "value":alphapercent,
                "percentile":rlfData.player.ordinals.alpha,
                "percent":rlfData.player.percentiles.alpha
            }
        ];

        rlf.makeRoleFits(roleFits);

        if (rlfData.player.seasonStats !== "undefined") {
            var currentStats = rlfData.player.seasonStats["2019"];

            $('#summary-stats').DataTable({
                "paging": false,
                "ordering": false,
                "searching": false,
                "info":false,
                "className":'compact',
                "columns": [
                    {title:"Avg", data: "avg", "defaultContent":"n/a"},
                    {title:"PPR", data: "points", "defaultContent":"n/a"},
                    {title:"Atts", data: "runs", "defaultContent":"n/a"},
                    {title:"Recs", data: "recs", "defaultContent":"n/a"},
                    {title:"Yds", data: "yds", "defaultContent":"n/a"},
                    {title:"Tds", data: "tds", "defaultContent":""}
                ],
                "data":[
                    {
                        "avg":currentStats.stats.pts_ppr_avg,
                        "points":currentStats.stats.pts_ppr,
                        "runs":currentStats.stats.rush_att,
                        "recs":currentStats.stats.rec,
                        "yds":(currentStats.stats.rec_yd + currentStats.stats.rush_yd),
                        "tds":currentStats.stats.all_td
                    },
                    {
                        "avg":currentStats.ranks.pts_ppr_avg,
                        "points":currentStats.ranks.pts_ppr,
                        "runs":currentStats.ranks.rush_att,
                        "recs":currentStats.ranks.rec,
                        "yds":currentStats.ranks.rush_yd,
                        "tds":currentStats.ranks.all_td
                    }
                ]
            });

            var seasonColumns = [
                {title: "Year", searchable: true, targets: 0, data: "year", "defaultContent":0},
                {title: "GP", data: "stats.gp", "defaultContent":0},
                {title: "PPR Points", data: "stats.pts_ppr", "defaultContent":0},
                {title: "PPR PPG", data: "stats.pts_ppr_avg", "defaultContent":0},
                {title: "Rank", data: "ranks.pts_ppr", "defaultContent":0},
                {title: "Rush Atts", data: "stats.rush_att", "defaultContent":0},
                {title: "Rush Yds", data: "stats.rush_yd", "defaultContent":0},
                {title: "Rush Tds", data: "stats.rush_td", "defaultContent":0},
                {title: "Recs", data: "stats.rec", "defaultContent":0},
                {title: "Tgts", data: "stats.rec_tgt", "defaultContent":0},
                {title: "Rec Yds", data: "stats.rec_yd", "defaultContent":0},
                {title: "Rec Tds", data: "stats.rec_td", "defaultContent":0},
                {title: "Rec Fds", data: "stats.rec_fd", "defaultContent":"N/A"},
                {title: "Rush Fds", data: "stats.rush_fd", "defaultContent":"N/A"},
            ];
            $('#season-stats').append("<tfoot><th colspan=\"2\">Career Average:<br>Career Total:</th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th></tfoot>");
            rlf.makeSeasonTable(seasonColumns);

            var gameLogColumns = [
                {title: "Year", searchable: true, data: "year"},
                {title: "Wk", data: "week"},
                {title: "PPR Points",  data: "stats.pts_ppr", "defaultContent":0},
                {title: "PRR Rank", data: "ranks.pts_ppr", "defaultContent":0},
                {title: "Rush Atts", data: "stats.rush_att", "defaultContent":0},
                {title: "Rush Yds", data: "stats.rush_yd", "defaultContent":0},
                {title: "Rush Tds", data: "stats.rush_td", "defaultContent":0},
                {title: "Recs", data: "stats.rec", "defaultContent":0},
                {title: "Tgts", data: "stats.rec_tgt", "defaultContent":0},
                {title: "Rec Yds", data: "stats.rec_yd", "defaultContent":0},
                {title: "Rec Tds", data: "stats.rec_td", "defaultContent":0},
                {title: "Rec 1st Downs", data: "stats.rec_fd", "defaultContent":0},
                {title: "Rush 1st Downs", data: "stats.rush_fd", "defaultContent":0}
            ];

            rlf.makeGameLogTable(gameLogColumns);
        }
    },

    initProsChartsRB : function(){
        var percent = rlfData.player.percentiles;
        var metrics = rlfData.player.metrics;
        var ordinals = rlfData.player.ordinals;
        // var avgLB = rlfData.average.LB;
        var chartData = {
            labels: ['Speed', 'Juke Agility', 'Route Agility', 'Elusiveness', 'Run Power', 'Speed Score'],
            datasets: [{
                type: 'bar',
                stack: 'Stack One',
                backgroundColor: 'rgb(29, 233, 195, 0.4)',
                label: 'RB Skills',
                borderWidth: 2,
                fill: false,
                data: [percent.fortyTime, percent.jukeAgility, percent.routeAgility, percent.elusiveness, percent.power, percent.speedScore],
                ordinals: [ordinals.fortyTime, ordinals.jukeAgility, ordinals.routeAgility, ordinals.elusiveness, ordinals.power, ordinals.speedScore],
                metrics: [metrics.fortyTime, metrics.jukeAgility, metrics.routeAgility, metrics.elusiveness, metrics.power, metrics.speedScore]
            },
                {
                    type: 'line',
                    backgroundColor: 'rgba(174, 3, 230, 0.25)',
                    fill: true,
                    label: 'Average NFL Inside Linebacker',
                    data: [53, 67, 61, 50, 55, 70],
                    ordinals: ["", "", "", "", "", "", ""],
                    metrics: ["", "", "", "", "", "", ""]
                },
            ]
        };
        rlf.makeProspectChart({});
        rlf.makeProspectChart(chartData);
    },

    initMesChartsRB : function(){
        var percent = rlfData.player.percentiles;
        var info = [percent.heightInches, percent.weight, percent.armsInches, percent.bmi, percent.fortyTime, percent.benchPress, percent.verticalJump, percent.broadJump, percent.cone, percent.shuttle];
        var labels =  ['height', 'weight', 'arms', 'bmi', '40', 'bench', 'vertical', 'broad', '3cone', 'shuttle'];
        rlf.makeRadarGraph(info,labels);
    },

    /****************************************** WR stuff **************************************************************/
    initWrPage : function() {
        rlf.initMesChartsWR();
        rlf.initProsChartsWR();
        //rlf.initOppChartsWR();

        if (rlfData.player.seasonStats !== undefined) {
            var seasonColumns = [
                {title: "Year", searchable: true, targets: 0, data: "year", "defaultContent":0},
                {title: "GP", data: "stats.gp", "defaultContent":0},
                {title: "Points", data: "stats.pts_ppr", "defaultContent":0},
                {title: "PPG", data:"stats.pts_ppr_avg", "defaultContent":0},
                {title: "Rank", data: "ranks.pts_ppr", "defaultContent":0},
                {title: "Recs", data: "stats.rec", "defaultContent":0},
                {title: "Tgts", data: "stats.rec_tgt", "defaultContent":0},
                {title: "Yds", data: "stats.rec_yd", "defaultContent":0},
                {title: "Tds", data: "stats.rec_td", "defaultContent":0},
                {title: "Fds", data: "stats.rec_fd", "defaultContent":0},
                {title: "YPR", data: "stats.rec_ypr", "defaultContent":0},
                {title: "YPT", data: "stats.rec_ypt", "defaultContent":0}
            ];
            $('#season-stats').append("<tfoot><th colspan=\"2\">Career Average:<br>Career Total:</th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th></tfoot>");
            rlf.makeSeasonTable(seasonColumns);

            var gameLogColumns = [
                {title: "Year", searchable: true, data: "year"},
                {title: "Wk", data: "week"},
                {title: "PPR Points",  data: "stats.pts_ppr", "defaultContent":0},
                {title: "PPR Rank", data: "ranks.pts_ppr", "defaultContent":0},
                {title: "Recs", data: "stats.rec", "defaultContent":0},
                {title: "Tgts", data: "stats.rec_tgt", "defaultContent":0},
                {title: "Yds", data: "stats.rec_yd", "defaultContent":0},
                {title: "Tds", data: "stats.rec_td", "defaultContent":0},
                {title: "Fds", data: "stats.rec_fd", "defaultContent":0},
                {title: "YPR", data: "stats.rec_ypr", "defaultContent":0},
                {title: "YPT", data: "stats.rec_ypt", "defaultContent":0}
            ];

            rlf.makeGameLogTable(gameLogColumns);

            var currentStats = rlfData.player.seasonStats["2019"];
            if (currentStats !== undefined) {
                $('#summary-stats').DataTable({
                    "paging": false,
                    "ordering": false,
                    "searching": false,
                    "info":false,
                    "className":'compact',
                    "columns": [
                        {title:"Avg", data: "avg", "defaultContent":"n/a"},
                        {title:"PPR", data: "points", "defaultContent":"n/a"},
                        {title:"Recs", data: "recs", "defaultContent":"n/a"},
                        {title:"Tgts", data: "targets", "defaultContent":"n/a"},
                        {title:"Yds", data: "yds", "defaultContent":"n/a"},
                        {title:"Tds", data: "tds", "defaultContent":""}
                    ],
                    "data":[
                        {
                            "avg":currentStats.stats.pts_ppr_avg,
                            "points":currentStats.stats.pts_ppr,
                            "recs":currentStats.stats.rec,
                            "targets":currentStats.stats.rec_tgt,
                            "yds":currentStats.stats.rec_yd,
                            "tds":currentStats.stats.all_td
                        },
                        {
                            "avg":currentStats.ranks.pts_ppr_avg,
                            "points":currentStats.ranks.pts_ppr,
                            "recs":currentStats.ranks.rec,
                            "targets":currentStats.ranks.rec_tgt,
                            "yds":currentStats.ranks.rec_yd,
                            "tds":currentStats.ranks.all_td
                        }
                    ]
                });
            }
        }

        var collegeColumns = [
            { title: "Year", data: "year", "defaultContent":0},
            { title: "College", data: "college", "defaultContent": "n/a"},
            { title: "Class", data: "class", "defaultContent": "n/a" },
            { title: "GP", data: "games", "defaultContent": 0 },
            { title: "Rec", data: "recs", "defaultContent": 0 },
            { title: "Rec Yds", data: "recYds", "defaultContent": 0},
            { title: "Rec Tds", data: "recTds", "defaultContent": 0},
            { title: "% of Recs", data: "recDom", "defaultContent": 0},
            { title: "% of Rec Yds", data: "ydsDom", "defaultContent": 0},
            { title: "% of Rec Tds", data: "tdsDom", "defaultContent": 0}
        ];

        rlf.makeCollegeTable(collegeColumns);

        var roleFits = [
            {
                "name":"Slot WR Score:",
                "value":rlfData.player.metrics.slot,
                "percentile":rlfData.player.ordinals.slot,
                "percent":rlfData.player.percentiles.slot
            },
            {
                "name":"Big Play Score:",
                "value":rlfData.player.metrics.deep,
                "percentile":rlfData.player.ordinals.deep,
                "percent":rlfData.player.percentiles.deep
            },
            {
                "name":"Outside X Score:",
                "value":rlfData.player.metrics.alpha,
                "percentile":rlfData.player.ordinals.alpha,
                "percent":rlfData.player.percentiles.alpha

            }
        ];

        rlf.makeRoleFits(roleFits);

        if (rlfData.player.metrics.collegeScore !== null) {
            $(".college-row-one p").text("Full Breakout Class: " + rlfData.player.metrics.breakoutClass);
            $(".college-row-two p").text("Dominate Seasons: " + rlfData.player.metrics.breakoutSeasons + " out of " + rlfData.player.metrics.collegeSeasons);
            $(".college-row-three p").text("Best Dominator: " + rlfData.player.metrics.bestDominator + "%");
            $(".donut-inner h5").text(rlfData.player.metrics.collegeScore);
            $(".donut-inner span").text(rlfData.player.ordinals.collegeScore + " percentile");
            var config = {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [rlfData.player.percentiles.collegeScore, Math.round(100 - rlfData.player.percentiles.collegeScore, 2)],
                        backgroundColor: ['rgba(174, 3, 230, 0.25)', 'white'],
                        label: 'College Score'
                    }],
                    labels: [
                        'College Score',
                        ''
                    ]
                },
                options: {
                    cutoutPercentage: 75,
                    legend: {
                        position: 'top'
                    },
                    title: {
                        display: false,
                        text: 'College Score'
                    },
                    responsive: true,
                    animation: {
                        animateScale: true,
                        animateRotate: true
                    }
                }
            };

            var ctx = document.getElementById('college-doughnut').getContext('2d');
            var myDoughut = new Chart(ctx, config);
        }

    },

    initProsChartsWR : function(){
        var percent = rlfData.player.percentiles;
        var metrics = rlfData.player.metrics;
        var ordinals = rlfData.player.ordinals;
        var chartData = {
            labels: ['College Score','Bully Score', 'Speed', 'Route Agility', 'Jumpball', 'Elusiveness', 'Run Power'],
            datasets: [
                {
                    type: 'line',
                    backgroundColor: 'rgba(174, 3, 230, 0.25)',
                    fill: true,
                    label: 'Average Corner',
                    data: [0, 44, 60, 54, 46, 55, 40],
                    ordinals: ["", "", "", "", "", "",""],
                    metrics: ["", "", "", "", "", "",""]
                },
                {
                    type: 'bar',
                    backgroundColor: 'rgb(29, 233, 195, 0.4)',
                    fontColor: 'rgba(174, 3, 230)',
                    labels: {
                        fontColor: 'rgba(174, 3, 230)'
                    },
                    label: 'WR Skills',
                    borderWidth: 2,
                    fill: false,
                    data: [percent.collegeScore, percent.bully, percent.fortyTime, percent.routeAgility, percent.jumpball, percent.elusiveness, percent.power],
                    ordinals: [ordinals.collegeScore, ordinals.bully, ordinals.fortyTime, ordinals.routeAgility, ordinals.jumpball, ordinals.elusiveness, ordinals.power ],
                    metrics: [metrics.collegeScore, metrics.bully, metrics.fortyTime, metrics.routeAgility, metrics.jumpball, metrics.elusiveness, metrics.power]
                }
            ]
        };
        rlf.makeProspectChart(chartData);
    },

    initMesChartsWR : function(){
        var percent = rlfData.player.percentiles;
        var info = [percent.heightInches, percent.weight, percent.armsInches, percent.bmi, percent.fortyTime, percent.benchPress, percent.verticalJump, percent.broadJump, percent.cone, percent.shuttle];
        var labels = ['height', 'weight', 'arms', 'bmi', '40', 'bench', 'vertical', 'broad', '3cone', 'shuttle'];
        rlf.makeRadarGraph(info, labels);
    },
    /****************************************** TE stuff **************************************************************/

    initTePage : function(){
        rlf.initProsChartsTE();
       // rlf.initOppChartsTE();
        rlf.initMesChartsTE();

        var collegeColumns = [
            { title: "Year", data: "year", "defaultContent":0},
            { title: "College", data: "college", "defaultContent": "n/a"},
            { title: "Class", data: "class", "defaultContent": "n/a" },
            { title: "GP", data: "games", "defaultContent": 0 },
            { title: "Rec", data: "recs", "defaultContent": 0 },
            { title: "Rec Yds", data: "recYds", "defaultContent": 0},
            { title: "Rec Tds", data: "recTds", "defaultContent": 0},
            { title: "% of Recs", data: "recDom", "defaultContent": 0},
            { title: "% of Rec Yds", data: "ydsDom", "defaultContent": 0},
            { title: "% of Rec Tds", data: "tdsDom", "defaultContent": 0}
        ];

        rlf.makeCollegeTable(collegeColumns);

        if (rlfData.player.seasonStats !== undefined) {
            var seasonColumns = [
                {title: "Year", searchable: true, targets: 0, data: "year", "defaultContent":0},
                {title: "GP", data: "stats.gp", "defaultContent":0},
                {title: "Points", data: "stats.pts_ppr", "defaultContent":0},
                {title: "PPG", data:"stats.pts_ppr_avg", "defaultContent":0},
                {title: "Rank", data: "ranks.pts_ppr", "defaultContent":0},
                {title: "Recs", data: "stats.rec", "defaultContent":0},
                {title: "Tgts", data: "stats.rec_tgt", "defaultContent":0},
                {title: "Yds", data: "stats.rec_yd", "defaultContent":0},
                {title: "Tds", data: "stats.rec_td", "defaultContent":0},
                {title: "Fds", data: "stats.rec_fd", "defaultContent":0},
                {title: "YPR", data: "stats.rec_ypr", "defaultContent":0},
                {title: "YPT", data: "stats.rec_ypt", "defaultContent":0}
            ];
            $('#season-stats').append("<tfoot><th colspan=\"2\">Career Average:<br>Career Total:</th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th></tfoot>");
            rlf.makeSeasonTable(seasonColumns);

            var gameLogColumns = [
                {title: "Year", searchable: true, data: "year"},
                {title: "Wk", data: "week"},
                {title: "PPR Points",  data: "stats.pts_ppr", "defaultContent":0},
                {title: "PPR Rank", data: "ranks.pts_ppr"},
                {title: "Recs", data: "stats.rec", "defaultContent":0},
                {title: "Tgts", data: "stats.rec_tgt", "defaultContent":0},
                {title: "Yds", data: "stats.rec_yd", "defaultContent":0},
                {title: "Tds", data: "stats.rec_td", "defaultContent":0},
                {title: "Fds", data: "stats.rec_fd", "defaultContent":0},
                {title: "YPR", data: "stats.rec_ypr", "defaultContent":0},
                {title: "YPT", data: "stats.rec_ypt", "defaultContent":0}
            ];

            rlf.makeGameLogTable(gameLogColumns);

            var currentStats = rlfData.player.seasonStats["2019"];
            if (currentStats != undefined) {
                $('#summary-stats').DataTable({
                    "paging": false,
                    "ordering": false,
                    "searching": false,
                    "info":false,
                    "className":'compact',
                    "columns": [
                        {title:"Avg", data: "avg", "defaultContent":"n/a"},
                        {title:"PPR", data: "points", "defaultContent":"n/a"},
                        {title:"Recs", data: "recs", "defaultContent":"n/a"},
                        {title:"Tgts", data: "targets", "defaultContent":"n/a"},
                        {title:"Yds", data: "yds", "defaultContent":"n/a"},
                        {title:"Tds", data: "tds", "defaultContent":""}
                    ],
                    "data":[
                        {
                            "avg":currentStats.stats.pts_ppr_avg,
                            "points":currentStats.stats.pts_ppr,
                            "recs":currentStats.stats.rec,
                            "targets":currentStats.stats.rec_tgt,
                            "yds":currentStats.stats.rec_yd,
                            "tds":currentStats.stats.all_td
                        },
                        {
                            "avg":currentStats.ranks.pts_ppr_avg,
                            "points":currentStats.ranks.pts_ppr,
                            "recs":currentStats.ranks.rec,
                            "targets":currentStats.ranks.rec_tgt,
                            "yds":currentStats.ranks.rec_yd,
                            "tds":currentStats.ranks.all_td
                        }
                    ]
                });
            }
        }


        var roleFits = [
            {
                "name":"Move TE",
                "value":Math.round(rlfData.player.metrics.move),
                "percentile": rlfData.player.ordinals.move,
                "percent": rlfData.player.percentiles.move
            },
            {
                "name":"In Line TE",
                "value":Math.round(rlfData.player.metrics.inLine),
                "percentile": rlfData.player.ordinals.inLine,
                "percent": rlfData.player.percentiles.inLine

            },
            {
                "name":"2 Way TE",
                "value":Math.round(rlfData.player.metrics.alpha),
                "percentile": rlfData.player.ordinals.alpha,
                "percent": rlfData.player.percentiles.move
            }
        ];

        rlf.makeRoleFits(roleFits);

        if (rlfData.player.metrics.collegeScore !== null) {
            $(".college-row-one p").text("Full Breakout Class: "+rlfData.player.metrics.breakoutClass);
            $(".college-row-two p").text("Dominate Seasons: "+rlfData.player.metrics.breakoutSeasons+" out of "+rlfData.player.metrics.collegeSeasons);
            $(".college-row-three p").text("Best Dominator: "+rlfData.player.metrics.bestDominator+"%");
            $(".donut-inner h5").text(rlfData.player.metrics.collegeScore);
            $(".donut-inner span").text(rlfData.player.ordinals.collegeScore+" percentile");
            var config = {
                type:'doughnut',
                data: {
                    datasets: [{
                        data: [rlfData.player.percentiles.collegeScore, Math.round(100 - rlfData.player.percentiles.collegeScore,2) ],
                        backgroundColor: ['rgba(174, 3, 230, 0.25)', 'white'],
                        label: 'College Score'
                    }],
                    labels: [
                        'College Score',
                        ''
                    ]
                },
                options: {
                    cutoutPercentage: 75,
                    legend: {
                        position:'top'
                    },
                    title: {
                        display:false,
                        text: 'College Score'
                    },
                    responsive: true,
                    animation: {
                        animateScale: true,
                        animateRotate: true
                    }
                }
            };

            var ctx = document.getElementById('college-doughnut').getContext('2d');
            var myDoughut = new Chart(ctx, config);
        }
    },

    initProsChartsTE : function(){
        var percent = rlfData.player.percentiles;
        var metrics = rlfData.player.metrics;
        var ordinals = rlfData.player.ordinals;
        var chartData = {
            labels: ['Speed', 'Route Agility', 'Jumpball', 'Elusiveness', 'Run Power', 'Bully Score', 'Run Block'],
            datasets: [{
                type: 'bar',
                stack: 'Stack One',
                label: 'TE Skills',
                backgroundColor: 'rgb(29, 233, 195, 0.4)',
                borderWidth: 2,
                fill: false,
                data: [percent.fortyTime, percent.routeAgility, percent.jumpball, percent.elusiveness, percent.power, percent.bully, percent.runBlock],
                ordinals: [ordinals.fortyTime, ordinals.routeAgility, ordinals.jumpball, ordinals.elusiveness, ordinals.power, ordinals.bully, ordinals.runBlock ],
                metrics: [metrics.fortyTime, metrics.routeAgility, metrics.jumpball, metrics.elusiveness, metrics.power, metrics.bully, metrics.runBlock]
            },
                {
                    type: 'line',
                    backgroundColor: 'rgba(174, 3, 230, 0.25)',
                    fill: true,
                    label: 'Average NFL Safety',
                    data: [70, 67, 61, 22, 15, 10, 9],
                    ordinals: ["", "", "", "", "", "", ""],
                    metrics: ["", "", "", "", "", "", ""]
                },
            ]
        };
        rlf.makeProspectChart(chartData);
    },

    initMesChartsTE : function(){
        var percent = rlfData.player.percentiles;
        var info = [percent.heightInches, percent.weight, percent.armsInches, percent.bmi, percent.fortyTime, percent.benchPress, percent.verticalJump, percent.broadJump, percent.cone, percent.shuttle];
        var labels = ['height', 'weight', 'arms', 'bmi', '40', 'bench', 'vertical', 'broad', '3cone', 'shuttle'];
        rlf.makeRadarGraph(info,labels);
    },

    /************************* OL Line **************************/
    initOlPage : function() {
        rlf.initProsChartsOl();
        rlf.initOppChartsTE();
        rlf.initMesChartsTE();

        var runBlock = Math.round((rlfData.player.metrics.runBlock));

        $(".role-one-bar .determinate").css("width", runBlock + "%");
        $(".role-one-title").text("Run Block");
        $(".role-one-score").text(runBlock + "%")
        if (runBlock > 69) {
            $(".role-one-bar .determinate").css("background-color", "green");
        }

        if (runBlock < 69 && runBlock > 40) {
            $(".role-one-bar .determinate").css("background-color", "yellow");
        }

        if (runBlock < 39) {
            $(".role-one-bar .determinate").css("background-color", "red");
        }

        var passBlock = Math.round((rlfData.player.metrics.insideBlock ));
        $(".role-two-bar .determinate").css("width", passBlock + "%");
        $(".role-two-title").text("Inisde Pass Block:");
        $(".role-two-score").text(passBlock + "%")
        if (passBlock > 69) {
            $(".role-two-bar .determinate").css("background-color", "green");
        }

        if (passBlock < 69 && passBlock > 40) {
            $(".role-two-bar .determinate").css("background-color", "yellow");
        }

        if (passBlock < 39) {
            $(".role-two-bar .determinate").css("background-color", "red");
        }


        var overAll = Math.round(rlfData.player.metrics.edgeBlock);
        $(".role-three-bar .determinate").css("width", overAll + "%");
        $(".role-three-title").text("Edge Pass Block:");
        $(".role-three-score").text(overAll + "%")
        if (overAll > 69) {
            $(".role-three-bar .determinate").css("background-color", "green");
        }

        if (overAll < 69 && overAll > 40) {
            $(".role-three-bar .determinate").css("background-color", "yellow");
        }

        if (overAll < 39) {
            $(".role-three-bar .determinate").css("background-color", "red");
        }
    },

    initProsChartsOl : function(){
        var percent = rlfData.player.percentiles;
        var metrics = rlfData.player.metrics;
        var ordinals = rlfData.player.ordinals;
        // var avgLB = rlfData.average.LB;
        var xValue = ['Bully Score', 'Speed', 'Agility','Power', 'Speed Score'];
        var yValue = [percent.bully, percent.fortyTime, '', percent.power, percent.speedScore];

        var trace1 = {
            x: xValue,
            y: yValue,
            name: 'OL Ability',
            type: 'bar',
            text: [
                metrics.bully+'<br>'+ordinals.bully+'%',
                metrics.fortyTime+'<br>'+ordinals.fortyTime+'%',
                '',
                metrics.power+'<br>'+ordinals.power+'%',
                metrics.speedScore+'<br>'+ordinals.speedScore+'%'
            ],
            textposition: 'auto',
            hoverinfo: 'none',
            opacity: 0.8,
        };

        var cone = percent.agility * .5;
        var shuttle = percent.agility * .5;
        var agilitypercent = Math.round(cone+shuttle);
        var coneTrace = {
            x:['Agility'],
            y: [cone],
            name: '3 cone ('+Math.round(percent.cone)+'%)',
            text: [
                metrics.cone
            ],
            textposition: 'auto',
            type: 'bar',
            marker: {
                color: 'rgb(158,202,225)',
                line: {
                    color: 'rgb(8,48,107)',
                    width: 1.5
                }
            }
        };

        var shuttleTrace = {
            x:['Agility'],
            y: [ shuttle],
            name: 'Shuttle ('+Math.round(percent.shuttle)+'%)',
            text: [
                metrics.shuttle
            ],
            textposition: 'auto',
            type: 'bar',
            marker: {
                color: 'rgba(58,200,225,.5)',
                line: {
                    color: 'rgb(8,48,107)',
                    width: 1.5
                }
            }
        };

        var trace2 = {
            x: xValue,
            y: [30, 89, 74, 45, 20],
            name: 'Average NFL Safety',
            type: 'scatter'
        };

        var lineBacker = {
            x: xValue,
            y: [70, 67, 61, 22, 41],
            name: 'Average NFL Safety',
            type: 'scatter'
        };

        var data = [trace1, trace2, coneTrace, shuttleTrace];

        var layout = {
            font: {size: 12},
            yaxis: {title: 'percentile', range: [0, 100]},
            yaxis2: {
                titlefont: {color: 'rgb(148, 103, 189)'},
                tickfont: {color: 'rgb(148, 103, 189)'},
                overlaying: 'y',
                side: 'right'
            },
            margin: {
                l: 50,
                r: 20,
                b: 20,
                t: 25,
                pad: 0
            },
            height: 350,
            barmode: 'stack',
            showlegend: true,
            legend: {
                x: 1,
                y: 0.5
            }
        };

        Plotly.newPlot('prospect-graph', data, layout, {responsive: true, displayModeBar: false});
    },
    /************************* OL Line **************************/
    initDlPage : function() {
        rlf.initProsChartsDl();
        rlf.initOppChartsTE();
        rlf.initMesChartsTE();

        var runBlock = Math.round((rlfData.player.metrics.runBlock));

        $(".role-one-bar .determinate").css("width", runBlock + "%");
        $(".role-one-title").text("Run Stuff");
        $(".role-one-score").text(runBlock + "%")
        if (runBlock > 69) {
            $(".role-one-bar .determinate").css("background-color", "green");
        }

        if (runBlock < 69 && runBlock > 40) {
            $(".role-one-bar .determinate").css("background-color", "yellow");
        }

        if (runBlock < 39) {
            $(".role-one-bar .determinate").css("background-color", "red");
        }

        var passBlock = Math.round((rlfData.player.metrics.insideBlock ));
        $(".role-two-bar .determinate").css("width", passBlock + "%");
        $(".role-two-title").text("Inside Pass Rush:");
        $(".role-two-score").text(passBlock + "%")
        if (passBlock > 69) {
            $(".role-two-bar .determinate").css("background-color", "green");
        }

        if (passBlock < 69 && passBlock > 40) {
            $(".role-two-bar .determinate").css("background-color", "yellow");
        }

        if (passBlock < 39) {
            $(".role-two-bar .determinate").css("background-color", "red");
        }


        var overAll = Math.round(rlfData.player.metrics.edgeBlock);
        $(".role-three-bar .determinate").css("width", overAll + "%");
        $(".role-three-title").text("Edge Pass Rush:");
        $(".role-three-score").text(overAll + "%")
        if (overAll > 69) {
            $(".role-three-bar .determinate").css("background-color", "green");
        }

        if (overAll < 69 && overAll > 40) {
            $(".role-three-bar .determinate").css("background-color", "yellow");
        }

        if (overAll < 39) {
            $(".role-three-bar .determinate").css("background-color", "red");
        }
    },

    initProsChartsDl : function(){
        var percent = rlfData.player.percentiles;
        var metrics = rlfData.player.metrics;
        var ordinals = rlfData.player.ordinals;
        // var avgLB = rlfData.average.LB;
        var xValue = ['Bully Score', 'Speed', 'Agility','Power', 'Speed Score'];
        var yValue = [percent.bully, percent.fortyTime, '', percent.power, percent.speedScore];

        var trace1 = {
            x: xValue,
            y: yValue,
            name: 'OL Ability',
            type: 'bar',
            text: [
                metrics.bully+'<br>'+ordinals.bully+'%',
                metrics.fortyTime+'<br>'+ordinals.fortyTime+'%',
                '',
                metrics.power+'<br>'+ordinals.power+'%',
                metrics.speedScore+'<br>'+ordinals.speedScore+'%'
            ],
            textposition: 'auto',
            hoverinfo: 'none',
            opacity: 0.8,
        };

        var cone = percent.agility * .5;
        var shuttle = percent.agility * .5;
        var agilitypercent = Math.round(cone+shuttle);
        var coneTrace = {
            x:['Agility'],
            y: [cone],
            name: '3 cone ('+Math.round(percent.cone)+'%)',
            text: [
                metrics.cone
            ],
            textposition: 'auto',
            type: 'bar',
            marker: {
                color: 'rgb(158,202,225)',
                line: {
                    color: 'rgb(8,48,107)',
                    width: 1.5
                }
            }
        };

        var shuttleTrace = {
            x:['Agility'],
            y: [ shuttle],
            name: 'Shuttle ('+Math.round(percent.shuttle)+'%)',
            text: [
                metrics.shuttle
            ],
            textposition: 'auto',
            type: 'bar',
            marker: {
                color: 'rgba(58,200,225,.5)',
                line: {
                    color: 'rgb(8,48,107)',
                    width: 1.5
                }
            }
        };

        var trace2 = {
            x: xValue,
            y: [30, 89, 74, 45, 20],
            name: 'Average NFL Safety',
            type: 'scatter'
        };

        var lineBacker = {
            x: xValue,
            y: [70, 67, 61, 22, 41],
            name: 'Average NFL Safety',
            type: 'scatter'
        };

        var data = [trace1, trace2, coneTrace, shuttleTrace];

        var layout = {
            font: {size: 12},
            yaxis: {title: 'percentile', range: [0, 100]},
            yaxis2: {
                titlefont: {color: 'rgb(148, 103, 189)'},
                tickfont: {color: 'rgb(148, 103, 189)'},
                overlaying: 'y',
                side: 'right'
            },
            margin: {
                l: 50,
                r: 20,
                b: 20,
                t: 25,
                pad: 0
            },
            height: 350,
            barmode: 'stack',
            showlegend: true,
            legend: {
                x: 1,
                y: 0.5
            }
        };

        Plotly.newPlot('prospect-graph', data, layout, {responsive: true, displayModeBar: false});
    },
    /************************* OLB Line **************************/
    initOlbPage : function() {
        rlf.initProsChartsOlb();
        rlf.initOppChartsTE();
        rlf.initMesChartsTE();

        var runStuff = Math.round((rlfData.player.metrics.runStuff));

        $(".role-one-bar .determinate").css("width", runStuff + "%");
        $(".role-one-title").text("Run Stuff");
        $(".role-one-score").text(runStuff + "%")
        if (runStuff > 69) {
            $(".role-one-bar .determinate").css("background-color", "green");
        }

        if (runStuff < 69 && runStuff > 40) {
            $(".role-one-bar .determinate").css("background-color", "yellow");
        }

        if (runStuff < 39) {
            $(".role-one-bar .determinate").css("background-color", "red");
        }

        var passBlock = Math.round((rlfData.player.metrics.edgeRush ));
        $(".role-two-bar .determinate").css("width", passBlock + "%");
        $(".role-two-title").text("Edge Pass Rush:");
        $(".role-two-score").text(passBlock + "%")
        if (passBlock > 69) {
            $(".role-two-bar .determinate").css("background-color", "green");
        }

        if (passBlock < 69 && passBlock > 40) {
            $(".role-two-bar .determinate").css("background-color", "yellow");
        }

        if (passBlock < 39) {
            $(".role-two-bar .determinate").css("background-color", "red");
        }


        var overAll = Math.round(rlfData.player.metrics.coverage);
        $(".role-three-bar .determinate").css("width", overAll + "%");
        $(".role-three-title").text("Coverage");
        $(".role-three-score").text(overAll + "%")
        if (overAll > 69) {
            $(".role-three-bar .determinate").css("background-color", "green");
        }

        if (overAll < 69 && overAll > 40) {
            $(".role-three-bar .determinate").css("background-color", "yellow");
        }

        if (overAll < 39) {
            $(".role-three-bar .determinate").css("background-color", "red");
        }
    },

    initProsChartsOlb : function(){
        var percent = rlfData.player.percentiles;
        var metrics = rlfData.player.metrics;
        var ordinals = rlfData.player.ordinals;
        // var avgLB = rlfData.average.LB;
        var xValue = ['Bully Score', 'Speed', 'Agility','Power', 'Speed Score'];
        var yValue = [percent.bully, percent.fortyTime, '', percent.power, percent.speedScore];

        var trace1 = {
            x: xValue,
            y: yValue,
            name: 'OL Ability',
            type: 'bar',
            text: [
                metrics.bully+'<br>'+ordinals.bully+'%',
                metrics.fortyTime+'<br>'+ordinals.fortyTime+'%',
                '',
                metrics.power+'<br>'+ordinals.power+'%',
                metrics.speedScore+'<br>'+ordinals.speedScore+'%'
            ],
            textposition: 'auto',
            hoverinfo: 'none',
            opacity: 0.8,
        };

        var cone = percent.agility * .5;
        var shuttle = percent.agility * .5;
        var agilitypercent = Math.round(cone+shuttle);
        var coneTrace = {
            x:['Agility'],
            y: [cone],
            name: '3 cone ('+Math.round(percent.cone)+'%)',
            text: [
                metrics.cone
            ],
            textposition: 'auto',
            type: 'bar',
            marker: {
                color: 'rgb(158,202,225)',
                line: {
                    color: 'rgb(8,48,107)',
                    width: 1.5
                }
            }
        };

        var shuttleTrace = {
            x:['Agility'],
            y: [ shuttle],
            name: 'Shuttle ('+Math.round(percent.shuttle)+'%)',
            text: [
                metrics.shuttle
            ],
            textposition: 'auto',
            type: 'bar',
            marker: {
                color: 'rgba(58,200,225,.5)',
                line: {
                    color: 'rgb(8,48,107)',
                    width: 1.5
                }
            }
        };

        var trace2 = {
            x: xValue,
            y: [30, 89, 74, 45, 20],
            name: 'Average NFL Safety',
            type: 'scatter'
        };

        var lineBacker = {
            x: xValue,
            y: [70, 67, 61, 22, 41],
            name: 'Average NFL Safety',
            type: 'scatter'
        };

        var data = [trace1, trace2, coneTrace, shuttleTrace];

        var layout = {
            font: {size: 12},
            yaxis: {title: 'percentile', range: [0, 100]},
            yaxis2: {
                titlefont: {color: 'rgb(148, 103, 189)'},
                tickfont: {color: 'rgb(148, 103, 189)'},
                overlaying: 'y',
                side: 'right'
            },
            margin: {
                l: 50,
                r: 20,
                b: 20,
                t: 25,
                pad: 0
            },
            height: 350,
            barmode: 'stack',
            showlegend: true,
            legend: {
                x: 1,
                y: 0.5
            }
        };

        Plotly.newPlot('prospect-graph', data, layout, {responsive: true, displayModeBar: false, animation: {duration: 250 * 1.5, easing: 'easeInQuad'}});
    },

    /************************* OLB Line **************************/
    initIlbPage : function() {
        rlf.initProsChartsIlb();
        rlf.initOppChartsTE();
        rlf.initMesChartsTE();

        var runStuff = Math.round((rlfData.player.metrics.runStop));

        $(".role-one-bar .determinate").css("width", runStuff + "%");
        $(".role-one-title").text("Run Stuff");
        $(".role-one-score").text(runStuff + "%")
        if (runStuff > 69) {
            $(".role-one-bar .determinate").css("background-color", "green");
        }

        if (runStuff < 69 && runStuff > 40) {
            $(".role-one-bar .determinate").css("background-color", "yellow");
        }

        if (runStuff < 39) {
            $(".role-one-bar .determinate").css("background-color", "red");
        }

        var passBlock = Math.round((rlfData.player.metrics.insideRush ));
        $(".role-two-bar .determinate").css("width", passBlock + "%");
        $(".role-two-title").text("Inside Pass Rush:");
        $(".role-two-score").text(passBlock + "%")
        if (passBlock > 69) {
            $(".role-two-bar .determinate").css("background-color", "green");
        }

        if (passBlock < 69 && passBlock > 40) {
            $(".role-two-bar .determinate").css("background-color", "yellow");
        }

        if (passBlock < 39) {
            $(".role-two-bar .determinate").css("background-color", "red");
        }


        var overAll = Math.round(rlfData.player.metrics.coverage);
        $(".role-three-bar .determinate").css("width", overAll + "%");
        $(".role-three-title").text("Coverage");
        $(".role-three-score").text(overAll + "%")
        if (overAll > 69) {
            $(".role-three-bar .determinate").css("background-color", "green");
        }

        if (overAll < 69 && overAll > 40) {
            $(".role-three-bar .determinate").css("background-color", "yellow");
        }

        if (overAll < 39) {
            $(".role-three-bar .determinate").css("background-color", "red");
        }
    },

    initProsChartsIlb : function(){
        var percent = rlfData.player.percentiles;
        var metrics = rlfData.player.metrics;
        var ordinals = rlfData.player.ordinals;
        // var avgLB = rlfData.average.LB;
        var xValue = ['Bully Score', 'Speed', 'Agility','Power', 'Speed Score'];
        var yValue = [percent.bully, percent.fortyTime, '', percent.power, percent.speedScore];

        var trace1 = {
            x: xValue,
            y: yValue,
            name: 'OL Ability',
            type: 'bar',
            text: [
                metrics.bully+'<br>'+ordinals.bully+'%',
                metrics.fortyTime+'<br>'+ordinals.fortyTime+'%',
                '',
                metrics.power+'<br>'+ordinals.power+'%',
                metrics.speedScore+'<br>'+ordinals.speedScore+'%'
            ],
            textposition: 'auto',
            hoverinfo: 'none',
            opacity: 0.8,
        };

        var cone = percent.agility * .5;
        var shuttle = percent.agility * .5;
        var agilitypercent = Math.round(cone+shuttle);
        var coneTrace = {
            x:['Agility'],
            y: [cone],
            name: '3 cone ('+Math.round(percent.cone)+'%)',
            text: [
                metrics.cone
            ],
            textposition: 'auto',
            type: 'bar',
            marker: {
                color: 'rgb(158,202,225)',
                line: {
                    color: 'rgb(8,48,107)',
                    width: 1.5
                }
            }
        };

        var shuttleTrace = {
            x:['Agility'],
            y: [ shuttle],
            name: 'Shuttle ('+Math.round(percent.shuttle)+'%)',
            text: [
                metrics.shuttle
            ],
            textposition: 'auto',
            type: 'bar',
            marker: {
                color: 'rgba(58,200,225,.5)',
                line: {
                    color: 'rgb(8,48,107)',
                    width: 1.5
                }
            }
        };

        var trace2 = {
            x: xValue,
            y: [30, 89, 74, 45, 20],
            name: 'Average NFL Safety',
            type: 'scatter'
        };

        var lineBacker = {
            x: xValue,
            y: [70, 67, 61, 22, 41],
            name: 'Average NFL Safety',
            type: 'scatter'
        };

        var data = [trace1, trace2, coneTrace, shuttleTrace];

        var layout = {
            font: {size: 12},
            yaxis: {title: 'percentile', range: [0, 100]},
            yaxis2: {
                titlefont: {color: 'rgb(148, 103, 189)'},
                tickfont: {color: 'rgb(148, 103, 189)'},
                overlaying: 'y',
                side: 'right'
            },
            margin: {
                l: 50,
                r: 20,
                b: 20,
                t: 25,
                pad: 0
            },
            height: 350,
            barmode: 'stack',
            showlegend: true,
            legend: {
                x: 1,
                y: 0.5
            }
        };

        Plotly.newPlot('prospect-graph', data, layout, {responsive: true, displayModeBar: false});
    },
    /******************************* Other Functions ***************************************/

    initSearch : function(){
        var list = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('full_name'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            // `states` is an array of state names defined in "The Basics"
            remote: {
                url: '/player/query/%QUERY',
                wildcard: '%QUERY'
            },
            dupDetector: function(remoteMatch, localMatch) {
                return remoteMatch.id === localMatch.id;
            }
        });


        $('.player-search .typeahead').typeahead(
            {
                hint: true,
                highlight: true,
                minLength: 1
            },
            {
                name: 'best-pictures',
                source: list,
                display: 'full_name'
            });

        $('#search-players .typeahead').on('typeahead:selected', function(evt, item){
            var url = "/player/view/"+item.id+"?"+item.nohash;
            window.location.href=url;
        });

        $('#edit-players .typeahead').on('typeahead:selected', function(evt, item){
            var url = "http://relllifefantasy/admin/editplayer/"+item.id;
            window.location.href=url;
        });

    },

    substringMatcher : function(strs) {
        return function findMatches(q, cb) {
            var matches, substringRegex;

            // an array that will be populated with substring matches
            matches = [];

            // regex used to determine if a string contains the substring `q`
            substrRegex = new RegExp(q, 'i');

            // iterate through the pool of strings and for any string that
            // contains the substring `q`, add it to the `matches` array
            $.each(strs, function (i, str) {
                if (substrRegex.test(str)) {
                    matches.push(str);
                }
            });

            cb(matches);
        };
    },

    getPath : function(level) {
        // Trig to calc meter point
        var degrees = 100 - level,
            radius = .5;
        var radians = degrees * Math.PI / 100;
        var x = radius * Math.cos(radians);
        var y = radius * Math.sin(radians);

        // Path: may have to change to create a better triangle
        var mainPath = 'M -.0 -0.025 L .0 0.025 L ',
            pathX = String(x),
            space = ' ',
            pathY = String(y),
            pathEnd = ' Z';
        var path = mainPath.concat(pathX,space,pathY,pathEnd);
        return path;
    },

    makeProspectChart: function(chartData) {
        var ctx = document.getElementById('canvas').getContext('2d');
        var options = {
            type: 'bar',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                aspectRatio: 1,
                title: {
                    display: false,
                    text: 'Chart.js Combo Bar Line Chart'
                },
                tooltips: {
                    mode: 'index',
                    intersect: true
                },
                legend: {
                    display: true,
                    labels: {
                        fontColor: 'rgba(174, 3, 230)'
                    },
                    position: 'bottom'
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            max: 100
                        },
                        gridLines: {
                            display: false
                        }
                    }],
                    xAxes: [{
                        gridLines: {
                            display: false
                        }
                    }]
                },
                layout: {
                    padding: {
                        left: 0,
                        right: 0,
                        top: 40,
                        bottom: 0
                    }
                },
                "animation": {
                    "duration": 600 * 1.5,
                    "easing": 'easeInQuad',
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
                                    var data = dataset.metrics[index]+'\n'+dataset.ordinals[index]+'%';
                                    ctx.fillText(data, bar._model.x, bar._model.y - 7);
                                }
                            });
                        });
                    }
                },
            }
        };

        window.myMixedChart = new Chart(ctx, options);
    },

    makeCollegeTable : function(columns) {
        $('#college-stats').DataTable( {
            "info":false,
            "paging":   false,
            "ordering": false,
            "searching": false,
            "className":"",
            data: rlfData.player.collegeTable,
            columns: columns,
            // "footerCallback": function ( row, data, start, end, display ) {
            //     if (data.length === 0) {
            //         return;
            //     }
            //
            //     var api = this.api(), data;
            //
            //     var intVal = function ( i ) {
            //         return typeof i === 'string' ?
            //             i.replace(/[\$,]/g, '')*1 :
            //             typeof i === 'number' ?
            //                 i : 0;
            //     };
            //
            //     var rowsToSum = [4,5,6];
            //     var selectedRows = api.rows('.selected').indexes();
            //     rowsToSum.forEach(function(col) {
            //         // Total filtered rows on the selected column (code part added)
            //         if (selectedRows.count() == 0) {
            //             total = api
            //                 .column( col, { page: 'current'} )
            //                 .data()
            //                 .reduce( function (a, b) {
            //                     return intVal(a) + intVal(b);
            //                 }, 0 );
            //             average = total/api.column( col, { page: 'current'} ).data().count();
            //         } else {
            //             total = api.cells( selectedRows, col, { page: 'current' } )
            //                 .data()
            //                 .reduce( function (a, b) {
            //                     return intVal(a) + intVal(b);
            //                 }, 0 );
            //             average = total/selectedRows.count();
            //         }
            //         $( api.column(col).footer() ).html(average.toFixed(1)+'<br>'+total.toFixed(1));
            //     });
            // }
        } );
    },

    makeSeasonTable: function(columns) {
        $('#season-stats').DataTable({
            "info":false,
            "paging": false,
            "ordering": false,
            "searching": false,
            data: rlfData.player.seasonTable,
            columns: columns,
            "footerCallback": function ( row, data, start, end, display ) {
                if (data.length == 0) {
                    return;
                }

                var api = this.api(), data;

                var intVal = function ( i ) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '').replace(/ *\([^)]*\) */g, "")*1 :
                        typeof i === 'number' ?
                            i : 0;
                };

                if (rlfData.player.position == "WR" || rlfData.player.position == "TE") {
                    var rowsToSum = [2,3,5,6,7,8,9,10,11];
                }

                if (rlfData.player.position == "RB") {
                    var rowsToSum = [2,3,5,6,7,8,9,10,11,12,13];
                }

                if (rlfData.player.position == "QB") {
                    var rowsToSum = [2,3,5,6,7,8,9,10,11,12,13,14,15,16];
                }

                var selectedRows = api.rows('.selected').indexes();
                rowsToSum.forEach(function(col) {
                    // Total filtered rows on the selected column (code part added)
                    if (selectedRows.count() == 0) {
                        total = api
                            .column( col, { page: 'current'} )
                            .data()
                            .reduce( function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0 );
                        average = total/api.column( col, { page: 'current'} ).data().count();
                    } else {
                        total = api.cells( selectedRows, col, { page: 'current' } )
                            .data()
                            .reduce( function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0 );
                        average = total/selectedRows.count();
                    }
                    $( api.column(col).footer() ).html(average.toFixed(1)+'<br>'+total.toFixed(1));
                });
            }
        });
    },

    colorDeterminates: function(){
        $(".determinate").each(function(){
            var widthPercent = Math.round(($(this).width()/$(this).parent().width()) * 100);
            if (widthPercent > 69.99) {
                $(this).css("background-color", "green");
            }

            if (widthPercent < 60 && widthPercent > 40) {
                $(this).css("background-color", "yellow");
            }

            if (widthPercent < 39.99) {
                $(this).css("background-color", "red");
            }
        });

    },

    makeGameLogTable: function(columns) {
        $('#game-logs').DataTable({
            "info":false,
            "paging": false,
            "ordering": false,
            data: rlfData.player.gameLogTable,
            columns: columns,
           // createdRow: function ( row, data, index ) {
            //     var newrow = this.api().row(index);
            //     newrow.child('<div><div id="rec-split"</div>').show();
            //     var recSplit = new Chart(ctx, {
            //         type: 'pie',
            //         data: {
            //             datasets: [{
            //                 data: [10, 20, 30]
            //             }],
            //
            //             // These labels appear in the legend and in the tooltips when hovering different arcs
            //             labels: [
            //                 'Red',
            //                 'Yellow',
            //                 'Blue'
            //             ]
            //         },
            //         options: {}
            //     });
            // },

            initComplete: function () {
                this.api().columns([0]).every( function () {
                    var column = this;
                    var select = $('<select><option value="">All</option></select>')
                        .on( 'change', function () {
                            var val = $.fn.dataTable.util.escapeRegex(
                                $(this).val()
                            );

                            column
                                .search( val ? '^'+val+'$' : '', true, false )
                                .draw();
                        } );

                    column.data().unique().each( function ( d, j ) {
                        select.append( '<option value="'+d+'">'+d+'</option>' )
                    } );

                    $("#game-logs_filter input").replaceWith(select);
                    $("#game-logs_filter select").formSelect();
                    if ( $("#game-logs_filter>select option").length > 0) {
                        $("#game-logs_filter select").val('2018').trigger("change");
                    }

                });
            },
            "footerCallback": function ( row, data, start, end, display ) {
                if (data.length === 0) {
                    return;
                }

                var api = this.api(), data;

                var intVal = function ( i ) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '')*1 :
                        typeof i === 'number' ?
                            i : 0;
                };

                var rowsToSum = [2,3,4,5,6,7,8];
                var selectedRows = api.rows('.selected').indexes();
                rowsToSum.forEach(function(col) {
                    // Total filtered rows on the selected column (code part added)
                    if (selectedRows.count() == 0) {
                        total = api
                            .column( col, { page: 'current'} )
                            .data()
                            .reduce( function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0 );
                        average = total/api.column( col, { page: 'current'} ).data().count();
                    } else {
                        total = api.cells( selectedRows, col, { page: 'current' } )
                            .data()
                            .reduce( function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0 );
                        average = total/selectedRows.count();
                    }
                    $( api.column(col).footer() ).html(average.toFixed(1)+'<br>'+total.toFixed(1));
                });
            }
        });

        $('#game-logs tbody').on( 'click', 'tr', function () {
            $(this).toggleClass('selected');
            $('#game-logs').DataTable().draw(false);
        });
    },

    // makeCombineResults : function(){
    //     $(".forty-bar .determinate").css("width", rlfData.player.percentiles.fortyTime + "%");
    //     $(".forty-bar .combine-score").text(rlfData.player.score.fortyTime);
    //     $(".role-one-percentile").text("("+roleFits[0].percentile + " percentile)");
    //     if (roleFits[0].percent > 69) {
    //         $(".role-one-bar .determinate").css("background-color", "green");
    //     }
    // };

    makeRoleFits : function(roleFits){
        $(".role-one-bar .determinate").css("width", roleFits[0].value + "%");
        $(".role-one-title").text(roleFits[0].name);
        $(".role-one-score").text(roleFits[0].value);
        $(".role-one-percentile").text("("+roleFits[0].percentile + " percentile)");
        if (roleFits[0].percent > 69) {
            $(".role-one-bar .determinate").css("background-color", "green");
        }


        if (roleFits[0].percent < 69 && roleFits[0].percent > 40) {
            $(".role-one-bar .determinate").css("background-color", "yellow");
        }

        if (roleFits[0].percent < 39) {
            $(".role-one-bar .determinate").css("background-color", "red");
        }

        $(".role-two-bar .determinate").css("width", roleFits[1].value + "%");
        $(".role-two-title").text(roleFits[1].name);
        $(".role-two-score").text(roleFits[1].value)
        $(".role-two-percentile").text("("+roleFits[1].percentile + " percentile)");
        if (roleFits[1].percent > 69) {
            $(".role-two-bar .determinate").css("background-color", "green");
        }

        if (roleFits[1].percent < 69 && roleFits[1].percent > 40) {
            $(".role-two-bar .determinate").css("background-color", "yellow");
        }

        if (roleFits[1].percent < 39) {
            $(".role-two-bar .determinate").css("background-color", "red");
        }

        $(".role-three-bar .determinate").css("width", roleFits[2].value + "%");
        $(".role-three-title").text(roleFits[2].name);
        $(".role-three-score").text(roleFits[2].value)
        $(".role-three-percentile").text("("+roleFits[2].percentile + " percentile)");
        if (roleFits[2].percent > 69) {
            $(".role-three-bar .determinate").css("background-color", "green");
        }

        if (roleFits[2].percent < 69 && roleFits[2].percent > 40) {
            $(".role-three-bar .determinate").css("background-color", "yellow");
        }

        if (roleFits[2].percent < 39) {
            $(".role-three-bar .determinate").css("background-color", "red");
        }
    },

    makeRadarGraph: function(info, labels) {
        var data = [{
            type: 'scatterpolar',
            r: info,
            theta: labels,
            fill: 'toself',
            opacity: 0.5,
            marker: {
                color: 'rgb(29, 233, 195)'
            },
        }];

        var layout = {
            polar: {
                radialaxis: {
                    visible: true,
                    range: [0, 100]
                }
            },
            font: {size: 10},
            autosize: false,
            width: 330,
            height: 270,
            margin: {
                l: 0,
                r: 0,
                b: 15,
                t: 25,
                pad: 0
            },

            showlegend: false
        };

        if ($("body").hasClass("mobile")) {
            layout.width = 275;
            layout.height = 275;
            layout.font.size = 8;
            layout.margin.b = 40;
            layout.margin.t = 40;
            // layout.autosize = true;
        }

        Plotly.plot("radar-graph", data, layout, {responsive: true, displayModeBar: false, staticPlot: true});
        $("#radar-graph").addClass("scale-in");
    }




    // initOppChartsRB : function() {
    //     var level = 0,
    //         teamLevel = 0,
    //         fitTitle = "",
    //         teamTitle = "";
    //
    //     if (rlfData.player.role === "Alpha") {
    //         level = (rlfData.player.metrics.alpha / 20) * 100;
    //         teamLevel = (rlfData.player.scores.alpha_wr_score * 10);
    //         fitTitle = 'Alpha WR Fit';
    //         teamTitle = "Team Alpha WR Opportunity";
    //     }
    //
    //
    //     if (rlfData.player.role === "Grinder") {
    //         level = rlfData.player.metrics.slot * 10;
    //         teamLevel = rlfData.player.scores.slot_wr_score * 10;
    //         fitTitle = 'Slot WR Fit';
    //         teamTitle = "Team Grinder RB Opportunity";
    //     }
    //
    //     if (rlfData.player.role === "Pass") {
    //         level = rlfData.player.metrics.deep * 10;
    //         teamLevel = rlfData.player.scores.deep_wr_score * 10;
    //         fitTitle = 'Deep WR Fit';
    //         teamTitle = "Team Pass Catcher RB Opportunity";
    //     }
    //
    //     // Trig to calc meter point
    //     var fitPath = rlf.getPath(level);
    //     var volumePath = rlf.getPath(teamLevel);
    //     var supportPath = rlf.getPath(0);
    //
    //     var data = [{
    //         type: 'scatter',
    //         x: [0], y:[0],
    //         marker: {size: 20, color:'850000'},
    //         showlegend: false,
    //         name: 'Rating',
    //         text: level,
    //         hoverinfo: 'text+name'},
    //         { values: [20, 20, 20, 20, 20, 100],
    //             rotation: 90,
    //             text: ['Smash', 'Great', 'Good', 'Meh',
    //                 'Trash', ''],
    //             textinfo: 'text',
    //             textposition:'inside',
    //             marker: {colors:['rgba(14, 127, 0, .5)', 'rgba(107, 255, 4, .5)',
    //                     'rgba(251, 255, 4, .5)', 'rgba(242, 189, 11  , .5)',
    //                     'rgba(242, 51, 11, .8)',
    //                     'rgba(242, 51, 11, 0)']},
    //             labels: ['81-100', '61-80', '41-60', '21-40', '0-20', ''],
    //             hoverinfo: 'label',
    //             hole: .5,
    //             type: 'pie',
    //             showlegend: false
    //         }];
    //
    //     var layout = {
    //         font: {size: 10},
    //         shapes:[{
    //             type: 'path',
    //             fillcolor: '850000',
    //             line: {
    //                 color: '850000'
    //             }
    //         }],
    //         title: fitTitle,
    //         Speed: '0-100',
    //         height: 300,
    //         margin: {
    //             l: 10,
    //             r: 10,
    //             b: 10,
    //             t: 55,
    //             pad: 0
    //         },
    //         xaxis: {
    //             zeroline:false,
    //             showticklabels:false,
    //             showgrid: false,
    //             range: [-1, 1]
    //         },
    //         yaxis: {
    //             zeroline:false,
    //             showticklabels:false,
    //             showgrid: false,
    //             range: [-1, 1]
    //         }
    //     };
    //
    //     layout.shapes[0].path = fitPath;
    //     Plotly.newPlot('fit-graph', data, layout, {displayModeBar: false});
    //     layout.title = 'Projected Volume';
    //     layout.shapes[0].path = volumePath;
    //     Plotly.newPlot('volume-graph', data, layout, {displayModeBar: false});
    //     layout.shapes[0].path = supportPath;
    //     layout.title = 'Supporting Efficiency';
    //     Plotly.newPlot('support-graph', data, layout, {displayModeBar: false});
    //
    //
    //     var level = 0,
    //         teamLevel = 0,
    //         title = "",
    //         teamTitle = "";
    //
    //     if (rlfData.player.role === "Alpha") {
    //         level = rlfData.player.position_scores.alpha_score;
    //         teamLevel = rlfData.player.team_scores.alpha_score;
    //         title = 'Alpha WR Rating';
    //         teamTitle = "Team Alpha WR Opportunity";
    //     }
    //
    //     if (rlfData.player.role === "G") {
    //         level = rlfData.player.position_scores.grinder_score;
    //         teamLevel = rlfData.player.team_scores.grinder_score;
    //         title = 'Grinder RB Rating';
    //         teamTitle = "Team Grinder RB Opportunity";
    //     }
    //
    //     if (rlfData.player.role === "Pass Catcher") {
    //         level = rlfData.player.position_scores.pass_score;
    //         teamLevel = rlfData.player.team_scores.pass_score;
    //         title = 'Pass Catcher RB Rating';
    //         teamTitle = "Team Pass Catcher RB Opportunity";
    //     }
    //
    //     // Trig to calc meter point
    //     var level = 0;
    //     var degrees = 100 - level,
    //         radius = .5;
    //     var radians = degrees * Math.PI / 100;
    //     var x = radius * Math.cos(radians);
    //     var y = radius * Math.sin(radians);
    //
    //     // Path: may have to change to create a better triangle
    //     var mainPath = 'M -.0 -0.025 L .0 0.025 L ',
    //         pathX = String(x),
    //         space = ' ',
    //         pathY = String(y),
    //         pathEnd = ' Z';
    //     var path = mainPath.concat(pathX,space,pathY,pathEnd);
    //
    //     var data = [{
    //         type: 'scatter',
    //         x: [0], y:[0],
    //         marker: {size: 20, color:'850000'},
    //         showlegend: false,
    //         name: 'Rating',
    //         text: level,
    //         hoverinfo: 'text+name'},
    //         { values: [20, 20, 20, 20, 20, 100],
    //             rotation: 90,
    //             text: ['Smash', 'Great', 'Good', 'Meh',
    //                 'Trash', ''],
    //             textinfo: 'text',
    //             textposition:'inside',
    //             marker: {colors:['rgba(14, 127, 0, .5)', 'rgba(107, 255, 4, .5)',
    //                     'rgba(251, 255, 4, .5)', 'rgba(242, 189, 11  , .5)',
    //                     'rgba(242, 51, 11, .8)',
    //                     'rgba(242, 51, 11, 0)']},
    //             labels: ['81-100', '61-80', '41-60', '21-40', '0-20', ''],
    //             hoverinfo: 'label',
    //             hole: .5,
    //             type: 'pie',
    //             showlegend: false
    //         }];
    //
    //     var layout = {
    //         font: {size: 10},
    //         shapes:[{
    //             type: 'path',
    //             path: path,
    //             fillcolor: '850000',
    //             line: {
    //                 color: '850000'
    //             }
    //         }],
    //         title: 'Role Fit',
    //         Speed: '0-100',
    //         height: 300,
    //         margin: {
    //             l: 10,
    //             r: 10,
    //             b: 10,
    //             t: 55,
    //             pad: 0
    //         },
    //         xaxis: {
    //             zeroline:false,
    //             showticklabels:false,
    //             showgrid: false,
    //             range: [-1, 1]
    //         },
    //         yaxis: {
    //             zeroline:false,
    //             showticklabels:false,
    //             showgrid: false,
    //             range: [-1, 1]
    //         }
    //     };
    //
    //     Plotly.newPlot('fit-graph', data, layout, {displayModeBar: false});
    //     layout.title = 'Projected Volume';
    //     Plotly.newPlot('volume-graph', data, layout, {displayModeBar: false});
    //     layout.title = 'Supporting Efficiency';
    //     Plotly.newPlot('support-graph', data, layout, {displayModeBar: false});
    // },

    // initOppChartsWR : function() {
    //     var level = 0,
    //         teamLevel = 0,
    //         fitTitle = "",
    //         teamTitle = "";
    //
    //     if (rlfData.player.role === "Alpha") {
    //         level = (rlfData.player.metrics[0].alpha / 30) * 100;
    //         teamLevel = (rlfData.player.scores[0].alpha_wr_score * 10);
    //         fitTitle = 'Alpha WR Fit';
    //         teamTitle = "Team Alpha WR Opportunity";
    //     }
    //
    //     if (rlfData.player.role === "Posession") {
    //         level = (rlfData.player.metrics[0].alpha / 20) * 100;
    //         teamLevel = (rlfData.player.scores[0].alpha_wr_score * 10);
    //         fitTitle = 'Posession WR Fit';
    //         teamTitle = "Team Alpha WR Opportunity";
    //     }
    //
    //     if (rlfData.player.role === "Slot") {
    //         level = rlfData.player.metrics[0].slot * 10;
    //         teamLevel = rlfData.player.scores[0].slot_wr_score * 10;
    //         fitTitle = 'Slot WR Fit';
    //         teamTitle = "Team Grinder RB Opportunity";
    //     }
    //
    //     if (rlfData.player.role === "Deep") {
    //         level = rlfData.player.metrics[0].deep * 10;
    //         teamLevel = rlfData.player.scores[0].deep_wr_score * 10;
    //         fitTitle = 'Deep WR Fit';
    //         teamTitle = "Team Pass Catcher RB Opportunity";
    //     }
    //
    //     // Trig to calc meter point
    //     var fitPath = rlf.getPath(level);
    //     var volumePath = rlf.getPath(teamLevel);
    //     var supportPath = rlf.getPath(0);
    //
    //     var data = [{
    //         type: 'scatter',
    //         x: [0], y:[0],
    //         marker: {size: 20, color:'850000'},
    //         showlegend: false,
    //         name: 'Rating',
    //         text: level,
    //         hoverinfo: 'text+name'},
    //         { values: [20, 20, 20, 20, 20, 100],
    //             rotation: 90,
    //             text: ['Smash', 'Great', 'Good', 'Meh',
    //                 'Trash', ''],
    //             textinfo: 'text',
    //             textposition:'inside',
    //             marker: {colors:['rgba(14, 127, 0, .5)', 'rgba(107, 255, 4, .5)',
    //                     'rgba(251, 255, 4, .5)', 'rgba(242, 189, 11  , .5)',
    //                     'rgba(242, 51, 11, .8)',
    //                     'rgba(242, 51, 11, 0)']},
    //             labels: ['81-100', '61-80', '41-60', '21-40', '0-20', ''],
    //             hoverinfo: 'label',
    //             hole: .5,
    //             type: 'pie',
    //             showlegend: false
    //         }];
    //
    //     var layout = {
    //         font: {size: 10},
    //         shapes:[{
    //             type: 'path',
    //             fillcolor: '850000',
    //             line: {
    //                 color: '850000'
    //             }
    //         }],
    //         title: fitTitle,
    //         Speed: '0-100',
    //         height: 300,
    //         margin: {
    //             l: 10,
    //             r: 10,
    //             b: 10,
    //             t: 55,
    //             pad: 0
    //         },
    //         xaxis: {
    //             zeroline:false,
    //             showticklabels:false,
    //             showgrid: false,
    //             range: [-1, 1]
    //         },
    //         yaxis: {
    //             zeroline:false,
    //             showticklabels:false,
    //             showgrid: false,
    //             range: [-1, 1]
    //         }
    //     };
    //
    //     layout.shapes[0].path = fitPath;
    //     Plotly.newPlot('fit-graph', data, layout, {displayModeBar: false});
    //     layout.title = 'Projected Volume';
    //     layout.shapes[0].path = volumePath;
    //     Plotly.newPlot('volume-graph', data, layout, {displayModeBar: false});
    //     layout.shapes[0].path = supportPath;
    //     layout.title = 'Supporting Efficiency';
    //     Plotly.newPlot('support-graph', data, layout, {displayModeBar: false});
    // },


    // initOppChartsTE : function(){
    //     var level = 0,
    //         teamLevel = 0,
    //         fitTitle = "",
    //         teamTitle = "";
    //
    //     if (rlfData.player.role === "Alpha") {
    //         level = (rlfData.player.metrics[0].alpha / 30) * 100;
    //         teamLevel = (rlfData.player.scores[0].alpha_wr_score * 10);
    //         fitTitle = 'Alpha WR Fit';
    //         teamTitle = "Team Alpha WR Opportunity";
    //     }
    //
    //     if (rlfData.player.role === "Posession") {
    //         level = (rlfData.player.metrics[0].alpha / 20) * 100;
    //         teamLevel = (rlfData.player.scores[0].alpha_wr_score * 10);
    //         fitTitle = 'Posession WR Fit';
    //         teamTitle = "Team Alpha WR Opportunity";
    //     }
    //
    //     if (rlfData.player.role === "Slot") {
    //         level = rlfData.player.metrics[0].slot * 10;
    //         teamLevel = rlfData.player.scores[0].slot_wr_score * 10;
    //         fitTitle = 'Slot WR Fit';
    //         teamTitle = "Team Grinder RB Opportunity";
    //     }
    //
    //     if (rlfData.player.role === "Deep") {
    //         level = rlfData.player.metrics[0].deep * 10;
    //         teamLevel = rlfData.player.scores[0].deep_wr_score * 10;
    //         fitTitle = 'Deep WR Fit';
    //         teamTitle = "Team Pass Catcher RB Opportunity";
    //     }
    //
    //     // Trig to calc meter point
    //     var fitPath = rlf.getPath(level);
    //     var volumePath = rlf.getPath(teamLevel);
    //     var supportPath = rlf.getPath(0);
    //
    //     var data = [{
    //         type: 'scatter',
    //         x: [0], y:[0],
    //         marker: {size: 20, color:'850000'},
    //         showlegend: false,
    //         name: 'Rating',
    //         text: level,
    //         hoverinfo: 'text+name'},
    //         { values: [20, 20, 20, 20, 20, 100],
    //             rotation: 90,
    //             text: ['Smash', 'Great', 'Good', 'Meh',
    //                 'Trash', ''],
    //             textinfo: 'text',
    //             textposition:'inside',
    //             marker: {colors:['rgba(14, 127, 0, .5)', 'rgba(107, 255, 4, .5)',
    //                     'rgba(251, 255, 4, .5)', 'rgba(242, 189, 11  , .5)',
    //                     'rgba(242, 51, 11, .8)',
    //                     'rgba(242, 51, 11, 0)']},
    //             labels: ['81-100', '61-80', '41-60', '21-40', '0-20', ''],
    //             hoverinfo: 'label',
    //             hole: .5,
    //             type: 'pie',
    //             showlegend: false
    //         }];
    //
    //     var layout = {
    //         font: {size: 10},
    //         shapes:[{
    //             type: 'path',
    //             fillcolor: '850000',
    //             line: {
    //                 color: '850000'
    //             }
    //         }],
    //         title: fitTitle,
    //         Speed: '0-100',
    //         height: 300,
    //         margin: {
    //             l: 10,
    //             r: 10,
    //             b: 10,
    //             t: 55,
    //             pad: 0
    //         },
    //         xaxis: {
    //             zeroline:false,
    //             showticklabels:false,
    //             showgrid: false,
    //             range: [-1, 1]
    //         },
    //         yaxis: {
    //             zeroline:false,
    //             showticklabels:false,
    //             showgrid: false,
    //             range: [-1, 1]
    //         }
    //     };
    //
    //     layout.shapes[0].path = fitPath;
    //     Plotly.newPlot('fit-graph', data, layout, {displayModeBar: false});
    //     layout.title = 'Projected Volume';
    //     layout.shapes[0].path = volumePath;
    //     Plotly.newPlot('volume-graph', data, layout, {displayModeBar: false});
    //     layout.shapes[0].path = supportPath;
    //     layout.title = 'Supporting Efficiency';
    //     Plotly.newPlot('support-graph', data, layout, {displayModeBar: false});
    // },

};
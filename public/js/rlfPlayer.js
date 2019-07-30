//store all functions in object

var rlf =  {
    /*************************** Compare Player ***************/
    initComparePage : function(){
        rlf.initCompareMesChart();
        rlf.initCompareSearches();
        rlf.initCompareProspect();
        rlf.initCompareSkillset();
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
        var percent = rlfData.players[0].percentiles;
        var percent2 = rlfData.players[1].percentiles;

        var data = [
            {
                type: 'scatterpolar',
                r: [percent.height, percent.weight, percent.arms, percent.bmi, percent.fortyTime, percent.benchPress, percent.verticalJump, percent.broadJump, percent.cone, percent.shuttle],
                theta: ['height', 'weight', 'arms', 'bmi', '40', 'bench', 'vertical', 'broad', '3cone', 'shuttle'],
                fill: 'toself',
                name: rlfData.players[0].first_name+' '+rlfData.players[0].last_name
            },
            {
                type: 'scatterpolar',
                r: [percent2.height, percent2.weight, percent2.arms, percent2.bmi, percent2.fortyTime, percent2.benchPress, percent2.verticalJump, percent2.broadJump, percent2.cone, percent2.shuttle],
                theta: ['height', 'weight', 'arms', 'bmi', '40', 'bench', 'vertical', 'broad', '3cone', 'shuttle'],
                fill: 'toself',
                name: rlfData.players[1].first_name+' '+rlfData.players[1].last_name
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

        Plotly.plot("radar-graph", data, layout, {responsive: true, displayModeBar: false});
        $("#radar-graph").addClass("scale-in");
    },

    initCompareProspect : function(){

        /****** WR Traces ******/
        var xValue = ['Bully Score', 'Speed', 'Agility', 'Jumpball', 'YAC', 'College Score'];

        var percent1 = rlfData.players[0].percentiles;
        var metrics1 = rlfData.players[0].metrics;
        var cone1 = percent1.cone * .5;
        var shuttle1 = percent1.shuttle * .5;
        var agilityPercent1 = Math.round(cone1+shuttle1);
        var yacPower1 = percent1.power * .40;
        var yacElusive1 = percent1.elusiveness * .60;
        var yacPercent1 = Math.round(yacPower1+yacElusive1);
        var player1 = {
            x: xValue,
            y: [percent1.bully, percent1.fortyTime, agilityPercent1, percent1.jumpball, yacPercent1, percent1.collegeScore ],
            name: rlfData.players[0].first_name+' '+rlfData.players[0].last_name,
            type: 'bar',
            text: [
                metrics1.bully+'<br>'+Math.round(percent1.bully)+'%',
                metrics1.fortyTime+'<br>'+Math.round(percent1.fortyTime)+'%',
                metrics1.agility+'<br>'+Math.round(percent1.agility)+'%',
                metrics1.jumpball+'<br>'+Math.round(percent1.jumpball)+'%',
                yacPercent1
            ],
            textposition: 'auto',
            hoverinfo: 'none',
            opacity: 0.8,
            marker : {
                color: 'url(#gradient-horizontal) gray',
                line: {
                    color: 'rgb(8,48,107)',
                    width: 1.5
                }
            }
        };

        var percent2 = rlfData.players[1].percentiles;
        var metrics2 = rlfData.players[1].metrics;
        var cone2 = percent2.cone * .5;
        var shuttle2 = percent2.shuttle * .5;
        var agilityPercent2 = Math.round(cone2+shuttle2);
        var yacPower2 = percent2.power * .40;
        var yacElusive2 = percent2.elusiveness * .60;
        var yacPercent2 = Math.round(yacPower2+yacElusive2);
        var player2 = {
            x: xValue,
            y: [percent2.bully, percent2.fortyTime, agilityPercent2, percent2.jumpball, yacPercent2, percent2.collegeScore],
            name: rlfData.players[1].first_name+' '+rlfData.players[1].last_name,
            type: 'bar',
            text: [
                metrics2.bully+'<br>'+Math.round(percent2.bully)+'%',
                metrics2.fortyTime+'<br>'+Math.round(percent2.fortyTime)+'%',
                metrics2.agility+'<br>'+Math.round(percent2.agility)+'%',
                metrics2.jumpball+'<br>'+Math.round(percent2.jumpball)+'%'
            ],
            textposition: 'auto',
            hoverinfo: 'none',
            opacity: 0.8,
            marker : {
                color: 'url(#gradient-horizontal) gray',
                line: {
                    color: 'rgb(8,48,107)',
                    width: 1.5
                }
            }
        };

        var corner = {
            x: xValue,
            y: [44, 58, 54, 41],
            name: 'Average NFL CB',
            type: 'scatter'
        };

        var data = [player1, player2, corner];


        var layout = {
            font: {size: 12},
            yaxis: {title: 'Percentile', range: [0, 100]},
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
                t: 50,
                pad: 0
            },
            height: 380,
            showlegend: true,
            legend: {
                margin: {
                    t: 25
                },
                "orientation": "h",
            }
        };

        Plotly.newPlot('prospect-graph', data, layout, {responsive: true, displayModeBar: false});
    },

    initCompareSkillset : function(){
        var slotPercent = Math.round(rlfData.players[0].percentiles.slot);

        $("#player1-skill .role-one-bar .determinate").css("width", slotPercent + "%");
        $("#player1-skill .role-one-title").text("Slot Score:");
        $("#player1-skill .role-one-score").text(slotPercent + "%")
        if (slotPercent > 62) {
            $("#player1-skill .role-one-bar .determinate").css("background-color", "green");
        }

        if (slotPercent < 61 && slotPercent > 40) {
            $("#player1-skill .role-one-bar .determinate").css("background-color", "yellow");
        }

        if (slotPercent < 39) {
            $("#player1-skill .role-one-bar .determinate").css("background-color", "red");
        }

        var deepPercent = Math.round(rlfData.players[0].percentiles.deep);
        $("#player1-skill .role-two-bar .determinate").css("width", deepPercent + "%");
        $("#player1-skill .role-two-title").text("Deep Threat Score:");
        $("#player1-skill .role-two-score").text(deepPercent + "%")
        if (deepPercent > 62) {
            $("#player1-skill .role-two-bar .determinate").css("background-color", "green");
        }

        if (deepPercent < 61 && deepPercent > 40) {
            $("#player1-skill .role-two-bar .determinate").css("background-color", "yellow");
        }

        if (deepPercent < 39) {
            $("#player1-skill .role-two-bar .determinate").css("background-color", "red");
        }


        var alphaPercent = Math.round(rlfData.players[0].percentiles.alpha);
        $("#player1-skill .role-three-bar .determinate").css("width", alphaPercent + "%");
        $("#player1-skill .role-three-title").text("Outside X Score:");
        $("#player1-skill .role-three-score").text(alphaPercent + "%")
        if (alphaPercent > 69) {
            $("#player1-skill .role-three-bar .determinate").css("background-color", "green");
        }

        if (alphaPercent < 69 && alphaPercent > 40) {
            $("#player1-skill .role-three-bar .determinate").css("background-color", "yellow");
        }

        if (alphaPercent < 39) {
            $("#player1-skill .role-three-bar .determinate").css("background-color", "red");
        }

        /*** Player2 ***/
        var slotPercent = rlfData.players[1].percentiles.slot;

        $("#player2-skill .role-one-bar .determinate").css("width", slotPercent + "%");
        $("#player2-skill .role-one-title").text("Slot Score:");
        $("#player2-skill .role-one-score").text(slotPercent + "%")
        if (slotPercent > 62) {
            $("#player2-skill .role-one-bar .determinate").css("background-color", "green");
        }

        if (slotPercent < 61 && slotPercent > 40) {
            $("#player2-skill .role-one-bar .determinate").css("background-color", "yellow");
        }

        if (slotPercent < 39) {
            $("#player2-skill .role-one-bar .determinate").css("background-color", "red");
        }

        var deepPercent = rlfData.players[1].percentiles.deep;
        $("#player2-skill .role-two-bar .determinate").css("width", deepPercent + "%");
        $("#player2-skill .role-two-title").text("Deep Threat Score:");
        $("#player2-skill .role-two-score").text(deepPercent + "%")
        if (deepPercent > 62) {
            $("#player2-skill .role-two-bar .determinate").css("background-color", "green");
        }

        if (deepPercent < 61 && deepPercent > 40) {
            $("#player2-skill .role-two-bar .determinate").css("background-color", "yellow");
        }

        if (deepPercent < 39) {
            $("#player2-skill .role-two-bar .determinate").css("background-color", "red");
        }


        var alphaPercent = Math.round((rlfData.players[1].metrics.alpha / 30) * 100);
        $("#player2-skill .role-three-bar .determinate").css("width", alphaPercent + "%");
        $("#player2-skill .role-three-title").text("Outside X Score:");
        $("#player2-skill .role-three-score").text(alphaPercent + "%")
        if (alphaPercent > 69) {
            $("#player2-skill .role-three-bar .determinate").css("background-color", "green");
        }

        if (alphaPercent < 69 && alphaPercent > 40) {
            $("#player2-skill .role-three-bar .determinate").css("background-color", "yellow");
        }

        if (alphaPercent < 39) {
            $("#player2-skill .role-three-bar .determinate").css("background-color", "red");
        }
    },

    initCompareTables : function(){

    },

    /************************* QB Metrics **************************/
    initQbPage : function() {
        rlf.initProsChartsQB();
        rlf.initMesChartsQb();

        var roleFits = [
            {
                "name":"Arm Talent",
                "value": Math.round(rlfData.player.metrics.armTalent)
            },
            {
                "name":"Mobility",
                "value": Math.round(rlfData.player.metrics.mobility)
            },
            {
                "name":"PlayMaker",
                "value": Math.round(rlfData.player.metrics.playmaker)
            }
        ];

        rlf.makeRoleFits(roleFits);

        var seasonColumns = [
            {title: "Year", searchable: true, targets: 0, data: "year", "defaultContent":0},
            {title: "GP", data: "stats.gp", "defaultContent":0},
            {title: "Pts", data: "stats.pts_ppr", "defaultContent":0},
            {title: "Pass Yds", data: "stats.pass_yd", "defaultContent":0},
            {title: "Pass Tds", data: "stats.pass_td", "defaultContent":0},
            {title: "Pass Cmp", data: "stats.pass_cmp", "defaultContent":0},
            {title: "Pass Atts", data: "stats.pass_att", "defaultContent":0},
            {title: "Rush Yds", data: "stats.rush_yd", "defaultContent":0},
            {title: "Rush Tds", data: "stats.rush_td", "defaultContent":0},
            {title: "Rush Atts", data: "stats.rush_att", "defaultContent":0}
        ];

        rlf.makeSeasonTable(seasonColumns);

        var gameLogColumns = [
            {title: "Year", searchable: true, data: "year"},
            {title: "Week", data: "week"},
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
        var info = [percent.heightInches, percent.weight, percent.arms, percent.bmi, percent.fortyTime, percent.verticalJump, percent.broadJump, percent.cone, percent.shuttle];
        var labels = ['height', 'weight', 'arms', 'bmi', '40', 'vertical', 'broad', '3cone', 'shuttle'];
        rlf.makeRadarGraph(info,labels);
    },
    /****************************************** RB stuff **************************************************************/

    initRbPage : function(){
        //rlf.initOppChartsRB();
        rlf.initMesChartsRB();
        rlf.initProsChartsRB();

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

        var grinderPercent =(rlfData.player.metrics.grinder / 12) * 100;
        var passCatcherPercent = (rlfData.player.metrics.passCatcher / 12) * 100;
        var alphaPercent = Math.round((rlfData.player.metrics.alpha / 25) * 100);

        var roleFits = [
            {
                "name":"Grinder Score",
                "value":grinderPercent
            },
            {
                "name":"Pass Catcher",
                "value":passCatcherPercent
            },
            {
                "name":"Alpha Score",
                "value":alphaPercent
            }
        ];

        rlf.makeRoleFits(roleFits);

        var seasonColumns = [
            {title: "Year", searchable: true, targets: 0, data: "year", "defaultContent":0},
            {title: "GP", data: "stats.gp", "defaultContent":0},
            {title: "PPG", data: "stats.pts_ppr", "defaultContent":0},
            {title: "Rush Yds", data: "stats.rush_yd", "defaultContent":0},
            {title: "Rush Tds", data: "stats.rush_td", "defaultContent":0},
            {title: "Rush Atts", data: "stats.rush_att", "defaultContent":0},
            {title: "Recs", data: "stats.rec", "defaultContent":0},
            {title: "Tgts", data: "stats.rec_tgt", "defaultContent":0},
            {title: "Rec Yds", data: "stats.rec_yd", "defaultContent":0},
            {title: "Rec Tds", data: "stats.rec_td", "defaultContent":0}
        ];

        rlf.makeSeasonTable(seasonColumns);

        var gameLogColumns = [
            {title: "Year", searchable: true, data: "year"},
            {title: "Week", data: "week"},
            {title: "PPG",  data: "stats.pts_ppr", "defaultContent":0},
            {title: "Rush Yds", data: "stats.rush_yd", "defaultContent":0},
            {title: "Rush Tds", data: "stats.rush_td", "defaultContent":0},
            {title: "Rush Atts", data: "stats.rush_att", "defaultContent":0},
            {title: "Recs", data: "stats.rec", "defaultContent":0},
            {title: "Tgts", data: "stats.rec_tgt", "defaultContent":0},
            {title: "Rec Yds", data: "stats.rec_yd", "defaultContent":0},
            {title: "Rec Tds", data: "stats.rec_td", "defaultContent":0}
        ];

        rlf.makeGameLogTable(gameLogColumns);
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
                label: 'RB Skills',
                borderWidth: 2,
                fill: false,
                data: [percent.fortyTime, percent.jukeAgility, percent.routeAgility, percent.elusiveness, percent.power, percent.speedScore],
                ordinals: [ordinals.fortyTime, ordinals.jukeAgility, ordinals.routeAgility, ordinals.elusiveness, ordinals.power, ordinals.speedScore],
                metrics: [metrics.fortyTime, metrics.jukeAgility, metrics.routeAgility, metrics.elusiveness, metrics.power, metrics.speedScore]
            },
                {
                    type: 'line',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
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

    initMesChartsRB : function(){
        var percent = rlfData.player.percentiles;
        var info = [percent.heightInches, percent.weight, percent.arms, percent.bmi, percent.fortyTime, percent.benchPress, percent.verticalJump, percent.broadJump, percent.cone, percent.shuttle];
        var labels =  ['height', 'weight', 'arms', 'bmi', '40', 'bench', 'vertical', 'broad', '3cone', 'shuttle'];
        rlf.makeRadarGraph(info,labels);
    },

    /****************************************** WR stuff **************************************************************/
    initWrPage : function() {
        rlf.initMesChartsWR();
        rlf.initProsChartsWR();
        //rlf.initOppChartsWR();

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

        var seasonColumns = [
            {title: "Year", searchable: true, targets: 0, data: "year", "defaultContent":0},
            {title: "GP", data: "stats.gp", "defaultContent":0},
            {title: "PPG", data: "stats.pts_ppr", "defaultContent":0},
            {title: "Recs", data: "stats.rec", "defaultContent":0},
            {title: "Yds", data: "stats.rec_yd", "defaultContent":0},
            {title: "Tds", data: "stats.rec_td", "defaultContent":0},
            {title: "Tgts", data: "stats.rec_tgt", "defaultContent":0},
            {title: "YPR", data: "stats.rec_ypr", "defaultContent":0},
            {title: "YPT", data: "stats.rec_ypt", "defaultContent":0},
            {title: "Deep Yds", data: "stats.rec_ypt", "defaultContent":0}
        ];

        rlf.makeSeasonTable(seasonColumns);

        var gameLogColumns = [
            {title: "Year", searchable: true, data: "year"},
            {title: "Week", data: "week"},
            {title: "PPG",  data: "stats.pts_ppr", "defaultContent":0},
            {title: "Recs", data: "stats.rec", "defaultContent":0},
            {title: "Yds", data: "stats.rec_yd", "defaultContent":0},
            {title: "Tds", data: "stats.rec_td", "defaultContent":0},
            {title: "Tgts", data: "stats.rec_tgt", "defaultContent":0},
            {title: "YPR", data: "stats.rec_ypr", "defaultContent":0},
            {title: "YPT", data: "stats.rec_ypt", "defaultContent":0}
        ];

        rlf.makeGameLogTable(gameLogColumns);

        var roleFits = [
            {
                "name":"Slot Score",
                "value":rlfData.player.percentiles.slot,
            },
            {
                "name":"Deep Score",
                "value":rlfData.player.percentiles.deep,
            },
            {
                "name":"Alpha Score",
                "value":rlfData.player.percentiles.alpha,
            }
        ];

        rlf.makeRoleFits(roleFits);
    },

    initProsChartsWR : function(){
        var percent = rlfData.player.percentiles;
        var metrics = rlfData.player.metrics;
        var ordinals = rlfData.player.ordinals;
        var chartData = {
            labels: ['College Score','Bully Score', 'Speed', 'Route Agility', 'Jumpball', 'Elusiveness', 'Run Power'],
            datasets: [{
                    type: 'bar',
                    backgroundColor: 'rgb(29, 233, 195, 0.4)',
                    stack: 'Stack One',
                    label: 'WR Skills',
                    borderWidth: 2,
                    fill: false,
                    data: [percent.collegeScore, percent.bully, percent.fortyTime, percent.routeAgility, percent.jumpball, percent.elusiveness, percent.power],
                    ordinals: [ordinals.collegeScore, ordinals.bully, ordinals.fortyTime, ordinals.routeAgility, ordinals.jumpball, ordinals.elusiveness, ordinals.power ],
                    metrics: [metrics.collegeScore, metrics.bully, metrics.fortyTime, metrics.routeAgility, metrics.jumpball, metrics.elusiveness, metrics.power]
                },
                {
                    type: 'line',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    fill: true,
                    label: 'Average Corner',
                    data: [null, 44, 58, 54, 41, 53, 44],
                    ordinals: ["", "", "", "", ""]
                }
            ]
        };
        rlf.makeProspectChart(chartData);
    },

    initMesChartsWR : function(){
        var percent = rlfData.player.percentiles;
        var info = [percent.heightInches, percent.weight, percent.arms, percent.bmi, percent.fortyTime, percent.benchPress, percent.verticalJump, percent.broadJump, percent.cone, percent.shuttle];
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

        var seasonColumns = [
            {title: "Year", searchable: true, targets: 0, data: "year", "defaultContent":0},
            {title: "GP", data: "stats.gp", "defaultContent":0},
            {title: "PPG", data: "stats.pts_ppr", "defaultContent":0},
            {title: "Recs", data: "stats.rec", "defaultContent":0},
            {title: "Yds", data: "stats.rec_yd", "defaultContent":0},
            {title: "Tds", data: "stats.rec_td", "defaultContent":0},
            {title: "Tgts", data: "stats.rec_tgt", "defaultContent":0},
            {title: "YPR", data: "stats.rec_ypr", "defaultContent":0},
            {title: "YPT", data: "stats.rec_ypt", "defaultContent":0},
            {title: "Deep Yds", data: "stats.rec_ypt", "defaultContent":0}
        ];

        rlf.makeSeasonTable(seasonColumns);

        var gameLogColumns = [
            {title: "Year", searchable: true, data: "year"},
            {title: "Week", data: "week"},
            {title: "PPG",  data: "stats.pts_ppr", "defaultContent":0},
            {title: "Recs", data: "stats.rec", "defaultContent":0},
            {title: "Yds", data: "stats.rec_yd", "defaultContent":0},
            {title: "Tds", data: "stats.rec_td", "defaultContent":0},
            {title: "Tgts", data: "stats.rec_tgt", "defaultContent":0},
            {title: "YPR", data: "stats.rec_ypr", "defaultContent":0},
            {title: "YPT", data: "stats.rec_ypt", "defaultContent":0}
        ];

        rlf.makeGameLogTable(gameLogColumns);

        var roleFits = [
            {
                "name":"Move Score",
                "value":Math.round((rlfData.player.metrics.move / 15) * 100)
            },
            {
                "name":"In Line Score",
                "value":Math.round((rlfData.player.metrics.inLine / 10) * 100)
            },
            {
                "name":"Alpha Score",
                "value":Math.round((rlfData.player.metrics.alpha / 25) * 100)
            }
        ];

        rlf.makeRoleFits(roleFits)
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
                borderWidth: 2,
                fill: false,
                data: [percent.fortyTime, percent.routeAgility, percent.jumpball, percent.elusiveness, percent.power, percent.bully, percent.runBlock],
                ordinals: [ordinals.fortyTime, ordinals.routeAgility, ordinals.jumpball, ordinals.elusiveness, ordinals.power, ordinals.bully, ordinals.runBlock ],
                metrics: [metrics.fortyTime, metrics.routeAgility, metrics.jumpball, metrics.elusiveness, metrics.power, metrics.bully, metrics.runBlock]
            },
                {
                    type: 'line',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
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
        var info = [percent.heightInches, percent.weight, percent.arms, percent.bmi, percent.fortyTime, percent.benchPress, percent.verticalJump, percent.broadJump, percent.cone, percent.shuttle];
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
        var agilityPercent = Math.round(cone+shuttle);
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
            yaxis: {title: 'Percentile', range: [0, 100]},
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
        var agilityPercent = Math.round(cone+shuttle);
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
            yaxis: {title: 'Percentile', range: [0, 100]},
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
        var agilityPercent = Math.round(cone+shuttle);
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
            yaxis: {title: 'Percentile', range: [0, 100]},
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
        var agilityPercent = Math.round(cone+shuttle);
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
            yaxis: {title: 'Percentile', range: [0, 100]},
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
            prefetch: '../../data/names.json',
            remote: {
                url: '/player/query/%QUERY',
                wildcard: '%QUERY'
            },
            dupDetector: function(remoteMatch, localMatch) {
                return remoteMatch.id === localMatch.id;
            }
        });


        $('.player-search .typeahead').typeahead({
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
            var url = "http://relllifefantasy/player/view/"+item.nohash;
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
        window.myMixedChart = new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: {
                responsive: true,
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
                        fontColor: 'rgb(255, 99, 132)'
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
                        stacked: true,
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
                    "duration": 1,
                    "onComplete": function() {
                        var chartInstance = this.chart,
                            ctx = chartInstance.ctx;

                        ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontSize, Chart.defaults.global.defaultFontStyle, Chart.defaults.global.defaultFontFamily);
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'bottom';
                        ctx.fontColor = 'black';

                        this.data.datasets.forEach(function(dataset, i) {
                            var meta = chartInstance.controller.getDatasetMeta(i);
                            meta.data.forEach(function(bar, index) {
                                var data = dataset.metrics[index]+'\n'+dataset.ordinals[index]+'%';
                                ctx.fillText(data, bar._model.x, bar._model.y - 5);
                            });
                        });
                    }
                },
            }
        });
    },

    makeCollegeTable : function(columns) {
        $('#college-stats').DataTable( {
            "paging":   false,
            "ordering": false,
            "searching": false,
            data: rlfData.player.collegeTable,
            columns: columns,
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

                var rowsToSum = [4,5,6];
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
        } );
    },

    makeSeasonTable: function(columns) {
        $('#season-stats').DataTable({
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
    },

    makeGameLogTable: function(columns) {
        $('#game-logs').DataTable({
            "paging": false,
            "ordering": false,
            data: rlfData.player.gameLogTable,
            columns: columns,
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

    makeRoleFits : function(roleFits){
        $(".role-one-bar .determinate").css("width", roleFits[0].value + "%");
        $(".role-one-title").text(roleFits[0].name);
        $(".role-one-score").text(roleFits[0].value + "%");
        if (roleFits[0].value > 69) {
            $(".role-one-bar .determinate").css("background-color", "green");
        }

        if (roleFits[0].value < 69 && roleFits[0].value > 40) {
            $(".role-one-bar .determinate").css("background-color", "yellow");
        }

        if (roleFits[0].value < 39) {
            $(".role-one-bar .determinate").css("background-color", "red");
        }

        $(".role-two-bar .determinate").css("width", roleFits[1].value + "%");
        $(".role-two-title").text(roleFits[1].name);
        $(".role-two-score").text(roleFits[1].value + "%")
        if (roleFits[1].value > 69) {
            $(".role-two-bar .determinate").css("background-color", "green");
        }

        if (roleFits[1].value < 69 && roleFits[1].value > 40) {
            $(".role-two-bar .determinate").css("background-color", "yellow");
        }

        if (roleFits[1].value < 39) {
            $(".role-two-bar .determinate").css("background-color", "red");
        }

        $(".role-three-bar .determinate").css("width", roleFits[2].value + "%");
        $(".role-three-title").text(roleFits[2].name);
        $(".role-three-score").text(roleFits[2].value + "%")
        if (roleFits[2].value > 69) {
            $(".role-three-bar .determinate").css("background-color", "green");
        }

        if (roleFits[2].value < 69 && roleFits[2].value > 40) {
            $(".role-three-bar .determinate").css("background-color", "yellow");
        }

        if (roleFits[2].value < 39) {
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
            width: 400,
            height: 300,
            margin: {
                l: 0,
                r: 0,
                b: 25,
                t: 25,
                pad: 0
            },

            showlegend: false
        };

        Plotly.plot("radar-graph", data, layout, {responsive: true, displayModeBar: false});
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
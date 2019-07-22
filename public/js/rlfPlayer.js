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
    /****************************************** RB stuff **************************************************************/

    initRbPage : function(){
        rlf.initOppChartsRB();
        rlf.initMesChartsRB();
        rlf.initProsChartsRB();

        $('#college-stats').DataTable( {
            "paging":   false,
            "ordering": false,
            "searching": false,
            data: rlfData.player.collegeTable,
            columns: [
                { title: "Year" },
                { title: "College" },
                { title: "Class" },
                { title: "GP" },
                { title: "Carries" },
                { title: "Rush Yds" },
                { title: "YPC" },
                { title: "Rush Tds" },
                { title: "Recs" },
                { title: "Rec Yds" },
                { title: "YPR" },
                { title: "Rec Tds" },
                { title: "% of Carries" },
                { title: "% of Recs" },
                { title: "% of total yds" },
                { title: "% of total tds" },
            ]
        } );

        var grinderPercent =(rlfData.player.metrics.grinder / 12) * 100;

        $(".role-one-bar .determinate").css("width", grinderPercent + "%");
        $(".role-one-title").text("Grinder Score:");
        $(".role-one-score").text(Math.round(grinderPercent) + "%")
        if (grinderPercent > 69) {
            $(".role-one-bar .determinate").css("background-color", "green");
        }

        if (grinderPercent < 69 && grinderPercent > 40) {
            $(".role-one-bar .determinate").css("background-color", "yellow");
        }

        if (grinderPercent < 39) {
            $(".role-one-bar .determinate").css("background-color", "red");
        }

        var passCatcherPercent = (rlfData.player.metrics.passCatcher / 12) * 100;
        $(".role-two-bar .determinate").css("width", Math.round(passCatcherPercent) + "%");
        $(".role-two-title").text("Pass Catcher Score:");
        $(".role-two-score").text(Math.round(passCatcherPercent) + "%")
        if (passCatcherPercent > 64) {
            $(".role-two-bar .determinate").css("background-color", "green");
        }

        if (passCatcherPercent < 64 && passCatcherPercent > 40) {
            $(".role-two-bar .determinate").css("background-color", "yellow");
        }

        if (passCatcherPercent < 39) {
            $(".role-two-bar .determinate").css("background-color", "red");
        }

        var alphaPercent = Math.round((rlfData.player.metrics.alpha / 25) * 100);
        $(".role-three-bar .determinate").css("width", alphaPercent + "%");
        $(".role-three-title").text("Alpha Back Score:");
        $(".role-three-score").text(alphaPercent + "%")

        if (alphaPercent > 64) {
            $(".role-three-bar .determinate").css("background-color", "green");
        }

        if (alphaPercent < 64 && alphaPercent > 40) {
            $(".role-three-bar .determinate").css("background-color", "yellow");
        }

        if (alphaPercent < 39) {
            $(".role-three-bar .determinate").css("background-color", "red");
        }
    },

    initProsChartsRB : function(){
        var percent = rlfData.player.percentiles;
        var metrics = rlfData.player.metrics;
        var ordinals = rlfData.player.ordinals;
        // var avgLB = rlfData.average.LB;

        var xValue = ['Speed', 'Agility', 'Elusiveness', 'Run Power', 'Speed Score', 'Juke Agilty', 'Route Agiltiy'];

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

        var trace1 = {
            x: xValue,
            y: [percent.fortyTime, '', percent.elusiveness, percent.power, percent.speedScore, percent.jukeAgility, percent.routeAgility],
            name: 'Percentile',
            type: 'bar',
            text: [
                metrics.fortyTime+'<br>'+ordinals.fortyTime+'%',
                metrics.agility+'<br>'+ordinals.agility+'%',
                metrics.elusiveness+'<br>'+ordinals.elusiveness+'%',
                metrics.power+'<br>'+ordinals.power+'%',
                metrics.speedScore+'<br>'+ordinals.speedScore+'%',
                metrics.jukeAgility+'<br>'+ordinals.jukeAgility+'%',
                metrics.routeAgility+'<br>'+ordinals.routeAgility+'%',
            ],
            textposition: 'auto',
            hoverinfo: 'none'
        };

        var trace2 = {
            x: xValue,
            y: [30, 38, 50, 60,],
            name: 'Average NFL Linebacker',
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

    initMesChartsRB : function(){
        var percent = rlfData.player.percentiles;

        var data = [{
            type: 'scatterpolar',
            r: [ percent.heightInches, percent.weight, percent.arms, percent.bmi, percent.fortyTime, percent.benchPress, percent.verticalJump, percent.broadJump, percent.cone, percent.shuttle],
            theta: ['height', 'weight', 'arms', 'bmi', '40', 'bench', 'vertical', 'broad', '3cone', 'shuttle'],
            fill: 'toself'
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
    },

    initOppChartsRB : function() {
        var level = 0,
            teamLevel = 0,
            fitTitle = "",
            teamTitle = "";

        if (rlfData.player.role === "Alpha") {
            level = (rlfData.player.metrics.alpha / 20) * 100;
            teamLevel = (rlfData.player.scores.alpha_wr_score * 10);
            fitTitle = 'Alpha WR Fit';
            teamTitle = "Team Alpha WR Opportunity";
        }


        if (rlfData.player.role === "Grinder") {
            level = rlfData.player.metrics.slot * 10;
            teamLevel = rlfData.player.scores.slot_wr_score * 10;
            fitTitle = 'Slot WR Fit';
            teamTitle = "Team Grinder RB Opportunity";
        }

        if (rlfData.player.role === "Pass") {
            level = rlfData.player.metrics.deep * 10;
            teamLevel = rlfData.player.scores.deep_wr_score * 10;
            fitTitle = 'Deep WR Fit';
            teamTitle = "Team Pass Catcher RB Opportunity";
        }

        // Trig to calc meter point
        var fitPath = rlf.getPath(level);
        var volumePath = rlf.getPath(teamLevel);
        var supportPath = rlf.getPath(0);

        var data = [{
            type: 'scatter',
            x: [0], y:[0],
            marker: {size: 20, color:'850000'},
            showlegend: false,
            name: 'Rating',
            text: level,
            hoverinfo: 'text+name'},
            { values: [20, 20, 20, 20, 20, 100],
                rotation: 90,
                text: ['Smash', 'Great', 'Good', 'Meh',
                    'Trash', ''],
                textinfo: 'text',
                textposition:'inside',
                marker: {colors:['rgba(14, 127, 0, .5)', 'rgba(107, 255, 4, .5)',
                        'rgba(251, 255, 4, .5)', 'rgba(242, 189, 11  , .5)',
                        'rgba(242, 51, 11, .8)',
                        'rgba(242, 51, 11, 0)']},
                labels: ['81-100', '61-80', '41-60', '21-40', '0-20', ''],
                hoverinfo: 'label',
                hole: .5,
                type: 'pie',
                showlegend: false
            }];

        var layout = {
            font: {size: 10},
            shapes:[{
                type: 'path',
                fillcolor: '850000',
                line: {
                    color: '850000'
                }
            }],
            title: fitTitle,
            Speed: '0-100',
            height: 300,
            margin: {
                l: 10,
                r: 10,
                b: 10,
                t: 55,
                pad: 0
            },
            xaxis: {
                zeroline:false,
                showticklabels:false,
                showgrid: false,
                range: [-1, 1]
            },
            yaxis: {
                zeroline:false,
                showticklabels:false,
                showgrid: false,
                range: [-1, 1]
            }
        };

        layout.shapes[0].path = fitPath;
        Plotly.newPlot('fit-graph', data, layout, {displayModeBar: false});
        layout.title = 'Projected Volume';
        layout.shapes[0].path = volumePath;
        Plotly.newPlot('volume-graph', data, layout, {displayModeBar: false});
        layout.shapes[0].path = supportPath;
        layout.title = 'Supporting Efficiency';
        Plotly.newPlot('support-graph', data, layout, {displayModeBar: false});


        var level = 0,
            teamLevel = 0,
            title = "",
            teamTitle = "";

        if (rlfData.player.role === "Alpha") {
            level = rlfData.player.position_scores.alpha_score;
            teamLevel = rlfData.player.team_scores.alpha_score;
            title = 'Alpha WR Rating';
            teamTitle = "Team Alpha WR Opportunity";
        }

        if (rlfData.player.role === "G") {
            level = rlfData.player.position_scores.grinder_score;
            teamLevel = rlfData.player.team_scores.grinder_score;
            title = 'Grinder RB Rating';
            teamTitle = "Team Grinder RB Opportunity";
        }

        if (rlfData.player.role === "Pass Catcher") {
            level = rlfData.player.position_scores.pass_score;
            teamLevel = rlfData.player.team_scores.pass_score;
            title = 'Pass Catcher RB Rating';
            teamTitle = "Team Pass Catcher RB Opportunity";
        }

        // Trig to calc meter point
        var level = 0;
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

        var data = [{
            type: 'scatter',
            x: [0], y:[0],
            marker: {size: 20, color:'850000'},
            showlegend: false,
            name: 'Rating',
            text: level,
            hoverinfo: 'text+name'},
            { values: [20, 20, 20, 20, 20, 100],
                rotation: 90,
                text: ['Smash', 'Great', 'Good', 'Meh',
                    'Trash', ''],
                textinfo: 'text',
                textposition:'inside',
                marker: {colors:['rgba(14, 127, 0, .5)', 'rgba(107, 255, 4, .5)',
                        'rgba(251, 255, 4, .5)', 'rgba(242, 189, 11  , .5)',
                        'rgba(242, 51, 11, .8)',
                        'rgba(242, 51, 11, 0)']},
                labels: ['81-100', '61-80', '41-60', '21-40', '0-20', ''],
                hoverinfo: 'label',
                hole: .5,
                type: 'pie',
                showlegend: false
            }];

        var layout = {
            font: {size: 10},
            shapes:[{
                type: 'path',
                path: path,
                fillcolor: '850000',
                line: {
                    color: '850000'
                }
            }],
            title: 'Role Fit',
            Speed: '0-100',
            height: 300,
            margin: {
                l: 10,
                r: 10,
                b: 10,
                t: 55,
                pad: 0
            },
            xaxis: {
                zeroline:false,
                showticklabels:false,
                showgrid: false,
                range: [-1, 1]
            },
            yaxis: {
                zeroline:false,
                showticklabels:false,
                showgrid: false,
                range: [-1, 1]
            }
        };

        Plotly.newPlot('fit-graph', data, layout, {displayModeBar: false});
        layout.title = 'Projected Volume';
        Plotly.newPlot('volume-graph', data, layout, {displayModeBar: false});
        layout.title = 'Supporting Efficiency';
        Plotly.newPlot('support-graph', data, layout, {displayModeBar: false});
    },

    /****************************************** WR stuff **************************************************************/
    initWrPage : function() {
        rlf.initMesChartsWR();
        rlf.initProsChartsWR();
        rlf.initOppChartsWR();

        var slotPercent = rlfData.player.percentiles.slot;

        $(".role-one-bar .determinate").css("width", slotPercent + "%");
        $(".role-one-title").text("Slot Score:");
        $(".role-one-score").text(slotPercent + "%")
        if (slotPercent > 69) {
            $(".role-one-bar .determinate").css("background-color", "green");
        }

        if (slotPercent < 69 && slotPercent > 40) {
            $(".role-one-bar .determinate").css("background-color", "yellow");
        }

        if (slotPercent < 39) {
            $(".role-one-bar .determinate").css("background-color", "red");
        }

        var deepPercent = rlfData.player.percentiles.deep;
        $(".role-two-bar .determinate").css("width", deepPercent + "%");
        $(".role-two-title").text("Deep Threat Score:");
        $(".role-two-score").text(deepPercent + "%")
        if (deepPercent > 69) {
            $(".role-two-bar .determinate").css("background-color", "green");
        }

        if (deepPercent < 69 && deepPercent > 40) {
            $(".role-two-bar .determinate").css("background-color", "yellow");
        }

        if (deepPercent < 39) {
            $(".role-two-bar .determinate").css("background-color", "red");
        }


        var alphaPercent = Math.round((rlfData.player.metrics.alpha / 30) * 100);
        $(".role-three-bar .determinate").css("width", alphaPercent + "%");
        $(".role-three-title").text("Alpha Score:");
        $(".role-three-score").text(alphaPercent + "%")
        if (alphaPercent > 69) {
            $(".role-three-bar .determinate").css("background-color", "green");
        }

        if (alphaPercent < 69 && alphaPercent > 40) {
            $(".role-three-bar .determinate").css("background-color", "yellow");
        }

        if (alphaPercent < 39) {
            $(".role-three-bar .determinate").css("background-color", "red");
        }

        $('#college-stats').DataTable( {
            "paging":   false,
            "ordering": false,
            "searching": false,
            data: rlfData.player.collegeTable,
            columns: [
                { title: "Year" },
                { title: "College" },
                { title: "Class" },
                { title: "GP" },
                { title: "Rec" },
                { title: "Rec Yds" },
                { title: "Rec Tds" },
                { title: "YPR Avg" },
                { title: "% of Recs" },
                { title: "% of Rec Yds" },
                { title: "% of Rec Tds" },
                { title: "Return Yds" },
                { title: "Return Tds" },
            ],
            "footerCallback": function ( row, data, start, end, display ) {
                var api = this.api(), data;

                var intVal = function ( i ) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '')*1 :
                        typeof i === 'number' ?
                            i : 0;
                };

                var rowsToSum = [4,5,6,7,11,12];
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

        $('#season-stats').DataTable({
            "paging": false,
            "ordering": false,
            "searching": false,
            data: rlfData.player.seasonTable,
            columns: [
                {title: "Year", searchable: true, targets: 0},
                {title: "GP"},
                {title: "PPG"},
                {title: "Recs"},
                {title: "Yds"},
                {title: "Tds"},
                {title: "Tgts"},
                {title: "YPR"},
                {title: "YPT"},
                {title: "Deep Yds"}
            ],
            "footerCallback": function ( row, data, start, end, display ) {
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


        $('#game-logs').DataTable({
            "paging": false,
            "ordering": false,
            data: rlfData.player.gameLogTable,
            columns: [
                {title: "Year", searchable: true},
                {title: "GP"},
                {title: "PPG"},
                {title: "Recs"},
                {title: "Yds"},
                {title: "Tds"},
                {title: "Tgts"},
                {title: "YPR"},
                {title: "YPT"},
            ],
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
                    $("#game-logs_filter select").val('2018').trigger("change");
                });
            },
            "footerCallback": function ( row, data, start, end, display ) {
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

        } );

    },


    initProsChartsWR : function(){
        var percent = rlfData.player.percentiles;
        var metrics = rlfData.player.metrics;
        var ordinals = rlfData.player.ordinals;
       // var avgLB = rlfData.average.LB;
        var xValue = ['Bully Score', 'Speed', 'Agility', 'Jumpball', 'juke agility', 'route agility'];
        var yValue = [percent.bully, percent.fortyTime, '', percent.jumpball, percent.jukeAgility, percent.routeAgility];

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


        var trace1 = {
            x: xValue,
            y: yValue,
            name: 'WR Ability',
            type: 'bar',
            text: [
                metrics.bully+'<br>'+ordinals.bully+'%',
                metrics.fortyTime+'<br>'+ordinals.fortyTime+'%',
                metrics.agility+'<br>'+ordinals.agility+'%',
                metrics.jumpball+'<br>'+ordinals.jumpball+'%'
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

        var trace2 = {
            x: xValue,
            y: [44, 58, 54, 41],
            name: 'Average NFL CB',
            type: 'scatter'
        };

        var yacPower = percent.power * .40;
        var yacElusive = percent.elusiveness * .60;
        var yacPercent = Math.round(yacPower+yacElusive);

        var trace3 = {
            x:['YAC'],
            y: [yacPower],
            name: 'Power ('+Math.round(percent.power)+'%)',
            text: [
                metrics.power
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

        var trace4 = {
            x:['YAC'],
            y: [yacElusive],
            name: 'Elusiveness ('+Math.round(percent.elusiveness)+'%)',
            text: [
                Math.round(yacPercent)+'%<br>'+metrics.elusiveness
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

        var trace5 = {
            x:['College'],
            y: [percent.collegeScore],
            name: 'College Score ('+Math.round(percent.collegeScore)+'%)',
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

        var data = [trace1, trace2, trace3, trace4, trace5, coneTrace, shuttleTrace];

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

    initMesChartsWR : function(){
        var percent = rlfData.player.percentiles;

        var data = [{
            type: 'scatterpolar',
            r: [ percent.heightInches, percent.weight, percent.arms, percent.bmi, percent.fortyTime, percent.benchPress, percent.verticalJump, percent.broadJump, percent.cone, percent.shuttle],
            theta: ['height', 'weight', 'arms', 'bmi', '40', 'bench', 'vertical', 'broad', '3cone', 'shuttle'],
            fill: 'toself'
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
            plot_bgcolor:'#000',
            showlegend: false
        };

        Plotly.plot("radar-graph", data, layout, {responsive: true, displayModeBar: false});
        $("#radar-graph").addClass("scale-in");
    },

    initOppChartsWR : function() {
        var level = 0,
            teamLevel = 0,
            fitTitle = "",
            teamTitle = "";

        if (rlfData.player.role === "Alpha") {
            level = (rlfData.player.metrics[0].alpha / 30) * 100;
            teamLevel = (rlfData.player.scores[0].alpha_wr_score * 10);
            fitTitle = 'Alpha WR Fit';
            teamTitle = "Team Alpha WR Opportunity";
        }

        if (rlfData.player.role === "Posession") {
            level = (rlfData.player.metrics[0].alpha / 20) * 100;
            teamLevel = (rlfData.player.scores[0].alpha_wr_score * 10);
            fitTitle = 'Posession WR Fit';
            teamTitle = "Team Alpha WR Opportunity";
        }

        if (rlfData.player.role === "Slot") {
            level = rlfData.player.metrics[0].slot * 10;
            teamLevel = rlfData.player.scores[0].slot_wr_score * 10;
            fitTitle = 'Slot WR Fit';
            teamTitle = "Team Grinder RB Opportunity";
        }

        if (rlfData.player.role === "Deep") {
            level = rlfData.player.metrics[0].deep * 10;
            teamLevel = rlfData.player.scores[0].deep_wr_score * 10;
            fitTitle = 'Deep WR Fit';
            teamTitle = "Team Pass Catcher RB Opportunity";
        }

        // Trig to calc meter point
        var fitPath = rlf.getPath(level);
        var volumePath = rlf.getPath(teamLevel);
        var supportPath = rlf.getPath(0);

        var data = [{
            type: 'scatter',
            x: [0], y:[0],
            marker: {size: 20, color:'850000'},
            showlegend: false,
            name: 'Rating',
            text: level,
            hoverinfo: 'text+name'},
            { values: [20, 20, 20, 20, 20, 100],
                rotation: 90,
                text: ['Smash', 'Great', 'Good', 'Meh',
                    'Trash', ''],
                textinfo: 'text',
                textposition:'inside',
                marker: {colors:['rgba(14, 127, 0, .5)', 'rgba(107, 255, 4, .5)',
                        'rgba(251, 255, 4, .5)', 'rgba(242, 189, 11  , .5)',
                        'rgba(242, 51, 11, .8)',
                        'rgba(242, 51, 11, 0)']},
                labels: ['81-100', '61-80', '41-60', '21-40', '0-20', ''],
                hoverinfo: 'label',
                hole: .5,
                type: 'pie',
                showlegend: false
            }];

        var layout = {
            font: {size: 10},
            shapes:[{
                type: 'path',
                fillcolor: '850000',
                line: {
                    color: '850000'
                }
            }],
            title: fitTitle,
            Speed: '0-100',
            height: 300,
            margin: {
                l: 10,
                r: 10,
                b: 10,
                t: 55,
                pad: 0
            },
            xaxis: {
                zeroline:false,
                showticklabels:false,
                showgrid: false,
                range: [-1, 1]
            },
            yaxis: {
                zeroline:false,
                showticklabels:false,
                showgrid: false,
                range: [-1, 1]
            }
        };

        layout.shapes[0].path = fitPath;
        Plotly.newPlot('fit-graph', data, layout, {displayModeBar: false});
        layout.title = 'Projected Volume';
        layout.shapes[0].path = volumePath;
        Plotly.newPlot('volume-graph', data, layout, {displayModeBar: false});
        layout.shapes[0].path = supportPath;
        layout.title = 'Supporting Efficiency';
        Plotly.newPlot('support-graph', data, layout, {displayModeBar: false});
    },
    /****************************************** TE stuff **************************************************************/

    initTePage : function(){
        rlf.initProsChartsTE();
        rlf.initOppChartsTE();
        rlf.initMesChartsTE();

        var movePercent = Math.round((rlfData.player.metrics.move / 15) * 100);

        $(".role-one-bar .determinate").css("width", movePercent + "%");
        $(".role-one-title").text("Move Score:");
        $(".role-one-score").text(movePercent + "%")
        if (movePercent > 69) {
            $(".role-one-bar .determinate").css("background-color", "green");
        }

        if (movePercent < 69 && movePercent > 40) {
            $(".role-one-bar .determinate").css("background-color", "yellow");
        }

        if (movePercent < 39) {
            $(".role-one-bar .determinate").css("background-color", "red");
        }

        var inLine = Math.round((rlfData.player.metrics.inLine / 10) * 100);
        $(".role-two-bar .determinate").css("width", inLine + "%");
        $(".role-two-title").text("In Line Score:");
        $(".role-two-score").text(inLine + "%")
        if (inLine > 69) {
            $(".role-two-bar .determinate").css("background-color", "green");
        }

        if (inLine < 69 && inLine > 40) {
            $(".role-two-bar .determinate").css("background-color", "yellow");
        }

        if (inLine < 39) {
            $(".role-two-bar .determinate").css("background-color", "red");
        }


        var alphaPercent = Math.round((rlfData.player.metrics.alpha / 25) * 100);
        $(".role-three-bar .determinate").css("width", alphaPercent + "%");
        $(".role-three-title").text("Alpha Score:");
        $(".role-three-score").text(alphaPercent + "%")
        if (alphaPercent > 69) {
            $(".role-three-bar .determinate").css("background-color", "green");
        }

        if (alphaPercent < 69 && alphaPercent > 40) {
            $(".role-three-bar .determinate").css("background-color", "yellow");
        }

        if (alphaPercent < 39) {
            $(".role-three-bar .determinate").css("background-color", "red");
        }

        // $('#college-stats').DataTable( {
        //     "paging":   false,
        //     "ordering": false,
        //     data: rlfData.player.collegeTable,
        //     columns: [
        //         { title: "Year" },
        //         { title: "College" },
        //         { title: "Class" },
        //         { title: "GP" },
        //         { title: "Rec" },
        //         { title: "Rec Yds" },
        //         { title: "Rec Tds" },
        //         { title: "YPR Avg" },
        //         { title: "% of Recs" },
        //         { title: "% of Rec Yds" },
        //         { title: "% of Rec Tds" }
        //     ]
        // } );
    },

    initProsChartsTE : function(){
        var percent = rlfData.player.percentiles;
        var metrics = rlfData.player.metrics;
        var ordinals = rlfData.player.ordinals;
        // var avgLB = rlfData.average.LB;
        var xValue = ['Bully Score', 'Speed', 'Jumpball', 'Power', 'Run Block', 'Juke Agility', 'Route Agility'];
        var yValue = [percent.bully, percent.fortyTime, percent.jumpball, percent.power, percent.runBlock, percent.jukeAgility, percent.routeAgility];

        var trace1 = {
            x: xValue,
            y: yValue,
            name: 'WR Ability',
            type: 'bar',
            text: [
                metrics.bully+'<br>'+ordinals.bully+'%',
                metrics.fortyTime+'<br>'+ordinals.fortyTime+'%',
                metrics.jumpball+'<br>'+ordinals.jumpball+'%',
                metrics.power+'<br>'+ordinals.power+'%',
                metrics.runBlock+'<br>'+ordinals.runBlock+'%',
                metrics.jukeAgility+'<br>'+ordinals.jukeAgility+'%',
                metrics.routeAgility+'<br>'+ordinals.routeAgility+'%',
            ],
            textposition: 'auto',
            hoverinfo: 'none',
            opacity: 0.8,
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

        //
        // var trace5 = {
        //     x:['College'],
        //     y: [percent.collegeScore],
        //     name: 'College Score ('+Math.round(percent.collegeScore)+'%)',
        //     textposition: 'auto',
        //     type: 'bar',
        //     marker: {
        //         color: 'rgba(58,200,225,.5)',
        //         line: {
        //             color: 'rgb(8,48,107)',
        //             width: 1.5
        //         }
        //     }
        // };

        var data = [trace1, trace2];

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

    initMesChartsTE : function(){
        var percent = rlfData.player.percentiles;

        var data = [{
            type: 'scatterpolar',
            r: [ percent.heightInches, percent.weight, percent.arms, percent.bmi, percent.fortyTime, percent.benchPress, percent.verticalJump, percent.broadJump, percent.cone, percent.shuttle],
            theta: ['height', 'weight', 'arms', 'bmi', '40', 'bench', 'vertical', 'broad', '3cone', 'shuttle'],
            fill: 'toself'
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
    },

    initOppChartsTE : function(){
        var level = 0,
            teamLevel = 0,
            fitTitle = "",
            teamTitle = "";

        if (rlfData.player.role === "Alpha") {
            level = (rlfData.player.metrics[0].alpha / 30) * 100;
            teamLevel = (rlfData.player.scores[0].alpha_wr_score * 10);
            fitTitle = 'Alpha WR Fit';
            teamTitle = "Team Alpha WR Opportunity";
        }

        if (rlfData.player.role === "Posession") {
            level = (rlfData.player.metrics[0].alpha / 20) * 100;
            teamLevel = (rlfData.player.scores[0].alpha_wr_score * 10);
            fitTitle = 'Posession WR Fit';
            teamTitle = "Team Alpha WR Opportunity";
        }

        if (rlfData.player.role === "Slot") {
            level = rlfData.player.metrics[0].slot * 10;
            teamLevel = rlfData.player.scores[0].slot_wr_score * 10;
            fitTitle = 'Slot WR Fit';
            teamTitle = "Team Grinder RB Opportunity";
        }

        if (rlfData.player.role === "Deep") {
            level = rlfData.player.metrics[0].deep * 10;
            teamLevel = rlfData.player.scores[0].deep_wr_score * 10;
            fitTitle = 'Deep WR Fit';
            teamTitle = "Team Pass Catcher RB Opportunity";
        }

        // Trig to calc meter point
        var fitPath = rlf.getPath(level);
        var volumePath = rlf.getPath(teamLevel);
        var supportPath = rlf.getPath(0);

        var data = [{
            type: 'scatter',
            x: [0], y:[0],
            marker: {size: 20, color:'850000'},
            showlegend: false,
            name: 'Rating',
            text: level,
            hoverinfo: 'text+name'},
            { values: [20, 20, 20, 20, 20, 100],
                rotation: 90,
                text: ['Smash', 'Great', 'Good', 'Meh',
                    'Trash', ''],
                textinfo: 'text',
                textposition:'inside',
                marker: {colors:['rgba(14, 127, 0, .5)', 'rgba(107, 255, 4, .5)',
                        'rgba(251, 255, 4, .5)', 'rgba(242, 189, 11  , .5)',
                        'rgba(242, 51, 11, .8)',
                        'rgba(242, 51, 11, 0)']},
                labels: ['81-100', '61-80', '41-60', '21-40', '0-20', ''],
                hoverinfo: 'label',
                hole: .5,
                type: 'pie',
                showlegend: false
            }];

        var layout = {
            font: {size: 10},
            shapes:[{
                type: 'path',
                fillcolor: '850000',
                line: {
                    color: '850000'
                }
            }],
            title: fitTitle,
            Speed: '0-100',
            height: 300,
            margin: {
                l: 10,
                r: 10,
                b: 10,
                t: 55,
                pad: 0
            },
            xaxis: {
                zeroline:false,
                showticklabels:false,
                showgrid: false,
                range: [-1, 1]
            },
            yaxis: {
                zeroline:false,
                showticklabels:false,
                showgrid: false,
                range: [-1, 1]
            }
        };

        layout.shapes[0].path = fitPath;
        Plotly.newPlot('fit-graph', data, layout, {displayModeBar: false});
        layout.title = 'Projected Volume';
        layout.shapes[0].path = volumePath;
        Plotly.newPlot('volume-graph', data, layout, {displayModeBar: false});
        layout.shapes[0].path = supportPath;
        layout.title = 'Supporting Efficiency';
        Plotly.newPlot('support-graph', data, layout, {displayModeBar: false});
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
    /************************* QB Metrics **************************/
    initQbPage : function() {
        rlf.initProsChartsQB();
        rlf.initOppChartsTE();
        rlf.initMesChartsQb();

        var runBlock = Math.round((rlfData.player.metrics.armTalent));

        $(".role-one-bar .determinate").css("width", runBlock + "%");
        $(".role-one-title").text("Arm Talent");
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

        var passBlock = Math.round((rlfData.player.metrics.mobility ));
        $(".role-two-bar .determinate").css("width", passBlock + "%");
        $(".role-two-title").text("Mobility:");
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


        var overAll = Math.round(rlfData.player.metrics.playmaker);
        $(".role-three-bar .determinate").css("width", overAll + "%");
        $(".role-three-title").text("Playmaker:");
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

        $('#college-stats').DataTable( {
            "paging":   false,
            "ordering": false,
            "searching": false,
            data: rlfData.player.collegeTable,
            columns: [
                { title: "Year" },
                { title: "College" },
                { title: "Class" },
                { title: "GP" },
                { title: "Cmp" },
                { title: "Att" },
                { title: "Pct" },
                { title: "Yds" },
                { title: "Ypa" },
                { title: "aypa" },
                { title: "tds" },
                { title: "ints" },
                { title: "rushAtt" },
                { title: "rushYds" },
                { title: "rushTds" }
            ]
        } );

        $('#season-stats').DataTable({
            "paging": false,
            "ordering": false,
            "searching": false,
            data: rlfData.player.seasonTable,
            columns: [
                {title: "Year", searchable: true, targets: 0},
                {title: "GP"},
                {title: "PPG"},
                {title: "Atts"},
                {title: "Cmp"},
                {title: "Yds"},
                {title: "TDs"},
                {title: "Ints"},
                {title: "PassFD"},
                {title: "RushAtts"},
                {title: "RushYds"},
                {title: "RushTDs"},
                {title: "RushFDs"}
            ],
            "footerCallback": function ( row, data, start, end, display ) {
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

    initProsChartsQB : function(){
        var percent = rlfData.player.percentiles;
        var metrics = rlfData.player.metrics;
        var ordinals = rlfData.player.ordinals;
        // var avgLB = rlfData.average.LB;
        var xValue = ['Speed', 'Agility','Break Tackles', 'Throw Power', 'Accuracy', 'Wonderlic'];
        var yValue = [percent.fortyTime, '', '', percent.throwVelocity, percent.depthAdjPct, percent.wonderlic];

        var trace1 = {
            x: xValue,
            y: yValue,
            name: 'QB Ability',
            type: 'bar',
            text: [
                metrics.fortyTime+'<br>'+ordinals.fortyTime+'%',
                '',
                metrics.elusiveness+'<br>'+ordinals.elusiveness+'%',
                metrics.throwVelocity+' mph<br>'+ordinals.throwVelocity+'%',
                metrics.depthAdjPct+'<br>'+ordinals.depthAdjPct+'%',
                metrics.wonderlic+'<br>'+ordinals.wonderlic+'%',
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

        var yacPower = percent.power * .50;
        var yacElusive = percent.elusiveness * .50;
        var yacPercent = Math.round(yacPower+yacElusive);

        var trace3 = {
            x:['Break Tackles'],
            y: [yacPower],
            name: 'Power ('+Math.round(percent.power)+'%)',
            text: [
                metrics.power
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

        var trace4 = {
            x:['Break Tackles'],
            y: [yacElusive],
            name: 'Elusiveness ('+Math.round(percent.elusiveness)+'%)',
            text: [
                Math.round(yacPercent)+'%<br>'+metrics.elusiveness
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


        var data = [trace1, coneTrace, shuttleTrace, trace3, trace4];

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

    initMesChartsQb : function(){
        var percent = rlfData.player.percentiles;

        var data = [{
            type: 'scatterpolar',
            r: [ percent.heightInches, percent.weight, percent.arms, percent.bmi, percent.fortyTime, percent.verticalJump, percent.broadJump, percent.cone, percent.shuttle],
            theta: ['height', 'weight', 'arms', 'bmi', '40', 'vertical', 'broad', '3cone', 'shuttle'],
            fill: 'toself'
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


        $('#custom-templates .typeahead').typeahead({
                hint: true,
                highlight: true,
                minLength: 1
            },
            {
                name: 'best-pictures',
                source: list,
                display: 'full_name'
            });

        $('#custom-templates .typeahead').on('typeahead:selected', function(evt, item){
            var url = "http://relllifefantasy/player/view/"+item.nohash;
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
    }
};
//store all functions in object

var rlf =  {
    initRbPage : function(){
        rlf.initOppChartsRB();
        rlf.initMesChartsRB();
        rlf.initProsChartsRB();
    },

    initWrPage : function() {
        rlf.initMesChartsWR();
        rlf.initProsChartsWR();
        rlf.initOppChartsWR();

        var slotPercent = rlfData.player.percentiles[0].slot;

        $(".slot-bar").css("width", slotPercent + "%");
        if (slotPercent > 69) {
            $(".slot-bar").addClass("bg-success");
        }

        if (slotPercent < 69 && slotPercent > 40) {
            $(".slot-bar").addClass("bg-warning");
        }

        if (slotPercent < 39) {
            $(".slot-bar").addClass("bg-danger");
        }

        var deepPercent = rlfData.player.percentiles[0].deep;

        $(".deep-bar").css("width", deepPercent + "%");
        if (deepPercent > 64) {
            $(".deep-bar").addClass("bg-success");
        }

        if (deepPercent < 64 && deepPercent > 40) {
            $(".deep-bar").addClass("bg-warning");
        }

        if (deepPercent < 39) {
            $(".deep-bar").addClass("bg-danger");
        }

        $('#college-stats').DataTable( {
            "paging":   false,
            "ordering": false,
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
                { title: "% of Return Yds" },
            ]
        } );
    },

    initTePage : function(){
        rlf.initProsChartsTE();
        rlf.initOppChartsTE();
        rlf.initMesChartsTE();
    },

    initSearch : function(){
        var states = ['Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California',
            'Colorado', 'Connecticut', 'Delaware', 'Florida', 'Georgia', 'Hawaii',
            'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana',
            'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota',
            'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire',
            'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota',
            'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island',
            'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont',
            'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming'
        ];


        var list = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('fullName'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            // `states` is an array of state names defined in "The Basics"
            local: rlfData.list,
        });


        $('#custom-templates .typeahead').typeahead({
                hint: true,
                highlight: true,
                minLength: 1
            },
            {
                name: 'best-pictures',
                source: list,
                display: 'fullName',
            });

        $('#custom-templates .typeahead').on('typeahead:selected', function(evt, item){
            var url = "http://relllifefantasy/player/view/"+item.alias;
            window.location.href=url;
        });
    },
    /**** RB Stuff ****/

    initProsChartsRB : function(){
        var percent = rlfData.player.percentiles[0];
        var metrics = rlfData.player.metrics[0];
        // var avgLB = rlfData.average.LB;

        var xValue = ['Speed', 'Agility', 'Elusiveness', 'Run Power', 'Block Power'];

        var trace1 = {
            x: xValue,
            y: [percent.fortyTime, percent.agility, percent.elusiveness, percent.power, ''],
            name: 'Percentile',
            type: 'bar',
            text: [
                metrics.fortyTime+'<br>'+Math.round(percent.fortyTime)+'%',
                metrics.agility+'<br>'+Math.round(percent.agility)+'%',
                metrics.elusiveness+'<br>'+Math.round(percent.elusiveness)+'%',
                metrics.power+'<br>'+Math.round(percent.power)+'%',
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
            autoexpand: true,
            margin: {
                t:10
            },
            width: 700,
            height: 350,
        };

        Plotly.newPlot('prospect', data, layout, {responsive: true, displayModeBar: false});
    },

    initMesChartsRB : function(){
        var percent = rlfData.player.percentiles[0];

        var data = [{
            type: 'scatterpolar',
            r: [ percent.height, percent.weight, percent.arms, percent.bmi, percent.fortyTime, percent.benchPress, percent.verticalJump, percent.broadJump, percent.agility],
            theta: ['height', 'weight', 'arms', 'bmi', '40', 'bench', 'vertical', 'broad', 'agility'],
            fill: 'toself'
        }];

        var layout = {
            polar: {
                radialaxis: {
                    visible: true,
                    range: [0, 100]
                }
            },
            title: 'Measurables',
            font: {size: 10},
            autosize: false,
            width: 300,
            height: 300,
            margin: {
                l: 40,
                r: 40,
                b: 0,
                t: 25,
                pad: 0
            },
            showlegend: false
        };

        Plotly.plot("measurables", data, layout, {responsive: true, displayModeBar: false});
    },

    initOppChartsRB : function(){
         var level = 0,
             teamLevel = 0,
             title = "",
             teamTitle = "";

         if (rlfData.player.role === "Alpha") {
             level = rlfData.player.position_scores.alpha_score;
             teamLevel = rlfData.player.team_scores.alpha_score;
             title = 'Alpha RB Rating';
             teamTitle = "Team Alpha RB Opportunity";
         }

         if (rlfData.player.role === "Grinder") {
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
        var degrees = 100 - level,
            radius = .5;
        var radians = degrees * Math.PI / 180;
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
                text: ['Elite', 'Great', 'Good', 'Meh',
                    'Trash', ''],
                textinfo: 'text',
                textposition:'inside',
                marker: {colors:['rgba(14, 127, 0, .5)', 'rgba(110, 154, 22, .5)',
                        'rgba(170, 202, 42, .5)', 'rgba(202, 209, 95, .5)',
                        'rgba(210, 206, 145, .5)',
                        'rgba(255, 255, 255, 0)']},
                labels: ['151-180', '121-150', '91-120', '61-90', '31-60', ''],
                hoverinfo: 'label',
                hole: .5,
                type: 'pie',
                showlegend: false
            }];

        var layout = {
            font: {size: 8},
            shapes:[{
                type: 'path',
                path: path,
                fillcolor: '850000',
                line: {
                    color: '850000'
                }
            }],
            title: title,
            Speed: '0-100',
            autosize: false,
            width: 200,
            height: 200,
            margin: {
                l: 10,
                r: 10,
                b: 0,
                t: 25,
                pad: 10
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

        Plotly.newPlot('fit', data, layout, {displayModeBar: false});

        // Trig to calc meter point
        var degrees2 = 100 - teamLevel,
            radius2 = .5;
        var radians2 = degrees2 * Math.PI / 100;
        var x2 = radius * Math.cos(radians2);
        var y2 = radius * Math.sin(radians2);

        // Path: may have to change to create a better triangle
        var mainPath2 = 'M -.0 -0.025 L .0 0.025 L ',
            pathX2 = String(x2),
            space2 = ' ',
            pathY2 = String(y2),
            pathEnd2 = ' Z';
        var path2 = mainPath2.concat(pathX2,space2,pathY2,pathEnd2);

        data.text = teamTitle;
        layout.shapes[0].path = path2;
        layout.title = 'Team Alpha RB Opportunity';

        Plotly.newPlot('fit2', data, layout, {displayModeBar: false});
    },

    /***** WR Stuff *****/
    initProsChartsWR : function(){
        var percent = rlfData.player.percentiles[0];
        var metrics = rlfData.player.metrics[0];
       // var avgLB = rlfData.average.LB;
        var xValue = ['Bully Score', 'Speed', 'Agility', 'Jumpball'];
        var yValue = [percent.bully, percent.fortyTime, percent.agility, percent.jumpball];

        var trace1 = {
            x: xValue,
            y: yValue,
            name: 'WR Ability',
            type: 'bar',
            text: [
                metrics.bully+'<br>'+Math.round(percent.bully)+'%',
                metrics.fortyTime+'<br>'+Math.round(percent.fortyTime)+'%',
                metrics.agility+'<br>'+Math.round(percent.agility)+'%',
                metrics.jumpball+'<br>'+Math.round(percent.jumpball)+'%'
            ],
            textposition: 'auto',
            hoverinfo: 'none',
            opacity: 0.8,
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

        var data = [trace1, trace2, trace3, trace4, trace5];

        var layout = {
            font: {size: 12},
            yaxis: {title: 'Percentile', range: [0, 100]},
            yaxis2: {
                titlefont: {color: 'rgb(148, 103, 189)'},
                tickfont: {color: 'rgb(148, 103, 189)'},
                overlaying: 'y',
                side: 'right'
            },
            autoexpand: true,
            margin: {
                t:10
            },
            width: 700,
            height: 350,
            barmode: 'stack'
        };

        Plotly.newPlot('prospect', data, layout, {responsive: true, displayModeBar: false});

    },

    initMesChartsWR : function(){
        var percent = rlfData.player.percentiles[0];

        var data = [{
            type: 'scatterpolar',
            r: [ percent.height, percent.weight, percent.arms, percent.bmi, percent.fortyTime, percent.benchPress, percent.verticalJump, percent.broadJump],
            theta: ['height', 'weight', 'arms', 'bmi', '40', 'bench', 'vertical', 'broad'],
            fill: 'toself'
        }];

        var layout = {
            polar: {
                radialaxis: {
                    visible: true,
                    range: [0, 100]
                }
            },
            title: 'Measurables',
            font: {size: 10},
            autosize: false,
            width: 300,
            height: 300,
            margin: {
                l: 40,
                r: 40,
                b: 0,
                t: 25,
                pad: 0
            },
            showlegend: false
        };

        Plotly.plot("measurables", data, layout, {responsive: true, displayModeBar: false});
    },

    initOppChartsWR : function(){
        var level = 0,
            teamLevel = 0,
            title = "",
            teamTitle = "";

        if (rlfData.player.role === "Alpha") {
            level = rlfData.player.position_scores.alpha_score;
            teamLevel = rlfData.player.team_scores.alpha_score;
            title = 'Alpha RB Rating';
            teamTitle = "Team Alpha RB Opportunity";
        }

        if (rlfData.player.role === "Grinder") {
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
        var degrees = 100 - level,
            radius = .5;
        var radians = degrees * Math.PI / 180;
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
                text: ['Elite', 'Great', 'Good', 'Meh',
                    'Trash', ''],
                textinfo: 'text',
                textposition:'inside',
                marker: {colors:['rgba(14, 127, 0, .5)', 'rgba(110, 154, 22, .5)',
                        'rgba(170, 202, 42, .5)', 'rgba(202, 209, 95, .5)',
                        'rgba(210, 206, 145, .5)',
                        'rgba(255, 255, 255, 0)']},
                labels: ['151-180', '121-150', '91-120', '61-90', '31-60', ''],
                hoverinfo: 'label',
                hole: .5,
                type: 'pie',
                showlegend: false
            }];

        var layout = {
            font: {size: 8},
            shapes:[{
                type: 'path',
                path: path,
                fillcolor: '850000',
                line: {
                    color: '850000'
                }
            }],
            title: title,
            Speed: '0-100',
            autosize: false,
            width: 200,
            height: 200,
            margin: {
                l: 10,
                r: 10,
                b: 0,
                t: 25,
                pad: 10
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

        Plotly.newPlot('fit', data, layout, {displayModeBar: false});

        // Trig to calc meter point
        var degrees2 = 100 - teamLevel,
            radius2 = .5;
        var radians2 = degrees2 * Math.PI / 100;
        var x2 = radius * Math.cos(radians2);
        var y2 = radius * Math.sin(radians2);

        // Path: may have to change to create a better triangle
        var mainPath2 = 'M -.0 -0.025 L .0 0.025 L ',
            pathX2 = String(x2),
            space2 = ' ',
            pathY2 = String(y2),
            pathEnd2 = ' Z';
        var path2 = mainPath2.concat(pathX2,space2,pathY2,pathEnd2);

        data.text = teamTitle;
        layout.shapes[0].path = path2;
        layout.title = 'Team Alpha RB Opportunity';

        Plotly.newPlot('fit2', data, layout, {displayModeBar: false});
    },

    /**** TE stuff ****/
    initProsChartsTE : function(){
        var percent = rlfData.player.percentiles;
        var avgLB = rlfData.average.LB;

        var xValue = ['Speed', 'Agility', 'Elusiveness', 'Run Power', 'Block Power'];

        var trace1 = {
            x: xValue,
            y: [percent.speed, percent.agility, percent.elusiveness, percent.run_power, percent.block_power],
            name: 'Percentile',
            type: 'bar'
        };

        var trace2 = {
            x: xValue,
            y: [avgLB.speed, avgLB.agility, avgLB.elusiveness, avgLB.run_power, avgLB.block_power],
            name: 'Average NFL Linebacker',
            type: 'scatter'
        };

        var data = [trace1, trace2];

        var layout = {
            font: {size: 12},
            yaxis: {title: 'Percentile'},
            yaxis2: {
                titlefont: {color: 'rgb(148, 103, 189)'},
                tickfont: {color: 'rgb(148, 103, 189)'},
                overlaying: 'y',
                side: 'right'
            },
            autoexpand: true,
            margin: {
                t:10
            },
            width: 700,
            height: 350,
        };

        Plotly.newPlot('prospect', data, layout, {responsive: true, displayModeBar: false});
    },

    initMesChartsTE : function(){
        var percent = rlfData.player.percentiles;

        var data = [{
            type: 'scatterpolar',
            r: [ percent.height, percent.weight, percent.arms, percent.bmi, percent.speed, percent.bench, percent.vertical, percent.broad],
            theta: ['height', 'weight', 'arms', 'bmi', '40', 'bench', 'vertical', 'broad'],
            fill: 'toself'
        }];

        var layout = {
            polar: {
                radialaxis: {
                    visible: true,
                    range: [0, 100]
                }
            },
            title: 'Measurables',
            font: {size: 10},
            autosize: false,
            width: 300,
            height: 300,
            margin: {
                l: 40,
                r: 40,
                b: 0,
                t: 25,
                pad: 0
            },
            showlegend: false
        };

        Plotly.plot("measurables", data, layout, {responsive: true, displayModeBar: false});
    },

    initOppChartsTE : function(){
        var level = 0,
            teamLevel = 0,
            title = "",
            teamTitle = "";

        if (rlfData.player.role === "Alpha") {
            level = rlfData.player.position_scores.alpha_score;
            teamLevel = rlfData.player.team_scores.alpha_score;
            title = 'Alpha RB Rating';
            teamTitle = "Team Alpha RB Opportunity";
        }

        if (rlfData.player.role === "Grinder") {
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
        var degrees = 100 - level,
            radius = .5;
        var radians = degrees * Math.PI / 180;
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
                text: ['Elite', 'Great', 'Good', 'Meh',
                    'Trash', ''],
                textinfo: 'text',
                textposition:'inside',
                marker: {colors:['rgba(14, 127, 0, .5)', 'rgba(110, 154, 22, .5)',
                        'rgba(170, 202, 42, .5)', 'rgba(202, 209, 95, .5)',
                        'rgba(210, 206, 145, .5)',
                        'rgba(255, 255, 255, 0)']},
                labels: ['151-180', '121-150', '91-120', '61-90', '31-60', ''],
                hoverinfo: 'label',
                hole: .5,
                type: 'pie',
                showlegend: false
            }];

        var layout = {
            font: {size: 8},
            shapes:[{
                type: 'path',
                path: path,
                fillcolor: '850000',
                line: {
                    color: '850000'
                }
            }],
            title: title,
            Speed: '0-100',
            autosize: false,
            width: 200,
            height: 200,
            margin: {
                l: 10,
                r: 10,
                b: 0,
                t: 25,
                pad: 10
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

        Plotly.newPlot('fit', data, layout, {displayModeBar: false});

        // Trig to calc meter point
        var degrees2 = 100 - teamLevel,
            radius2 = .5;
        var radians2 = degrees2 * Math.PI / 100;
        var x2 = radius * Math.cos(radians2);
        var y2 = radius * Math.sin(radians2);

        // Path: may have to change to create a better triangle
        var mainPath2 = 'M -.0 -0.025 L .0 0.025 L ',
            pathX2 = String(x2),
            space2 = ' ',
            pathY2 = String(y2),
            pathEnd2 = ' Z';
        var path2 = mainPath2.concat(pathX2,space2,pathY2,pathEnd2);

        data.text = teamTitle;
        layout.shapes[0].path = path2;
        layout.title = 'Team Alpha RB Opportunity';

        Plotly.newPlot('fit2', data, layout, {displayModeBar: false});
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
};
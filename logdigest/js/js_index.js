

var caminhos = []; 
var tec = [];   
var tipo = [];
function init(Y, inittec){      
    caminhos = inittec;
}

require(['core/first'], function(core){


ipelements = document.getElementById("id_instancia");
tecelements = document.getElementById("id_tecnologia");
tipoelements = document.getElementById("id_tipo");



function limparTec(){
    var length = tecelements.options.length;
    for (i = length-1; i >= 0; i--) {
        tecelements.options[i] = null;
    }
}

function carregarTec(){
    var optionArray = [];
    if (ipelements.value ||  ipelements.value!=-1 ){
        var temp = caminhos.filter(x => x.instanciaid === ipelements.value);


        for (var i =0; i<temp.length ; i++)
        {

            if (tec.indexOf(temp[i].tecnologia) !== -1){
                
            } else {
                tec.push(temp[i].tecnologia)
            }
            console.log(tec);
        }
    }

    for (var i =0; i<tec.length ; i++)
    {
        var newoption = document.createElement("option");
        newoption.value = tec[i];
        newoption.innerHTML = tec[i];
        tecelements.options.add(newoption);
    }
    
}




function limparTipo(){
    length = tipoelements.options.length;
    for (i = length-1; i >= 0; i--) {
        tipoelements.options[i] = null;
    }
}

function carregarTipo(){
    if (tecelements.value){
        var temp = caminhos.filter(x => x.instanciaid === ipelements.value);
        temp = temp.filter(x => x.tecnologia === tecelements.value);
        for (var i =0; i<temp.length ; i++)
        {

            if (tipo.indexOf(temp[i].tipo) !== -1){
                
            } else {
                tipo.push(temp[i].tipo)
            }
            console.log(tipo);
        }

        for (var i =0; i<tipo.length ; i++)
        {
            var newoption = document.createElement("option");
            newoption.value = tipo[i];
            newoption.innerHTML = tipo[i];
            tipoelements.options.add(newoption);
        }
        

    }
}





ipelements.addEventListener("change", function() {
    tec = [];
    tipo = [];
    tecelements.innerHTML = "";
    tipoelements.innerHTML = "";
 
    carregarTec();
    carregarTipo()

  });


  tecelements.addEventListener("change", function() {
    tipo = [];
    tipoelements.innerHTML = "";

    carregarTipo();


  });

});
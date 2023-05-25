document.addEventListener('DOMContentLoaded', function(){
    document.getElementById('mytype3001').addEventListener("change", function() {
        const rohwert = document.getElementById('mytype3001').value;
        const typearray = rohwert.split('#');
        if (parseInt(typearray[0]) > 0) {
            document.getElementById('mytype' + typearray[0]).checked = true;
            document.getElementById('mytypeval').value = typearray[1];
        }
    });
}, false);
 function datatable_for_student_data(Y, cols, data1){
     //console.log(data1);
     //console.log(JSON.parse(data1));
     data1 = JSON.parse(data1);
     
    YUI().use("datatable-sort", function(Y) {
        console.log(data1);
        var cols = [
            {key:"college", label:"Click to Sort by College", sortable:true},
            {key:"courseload", label:"Click to Sort by Course Load", sortable:true},
            {key:"id", label:"Click to Sort by ID", sortable:true},
            {key:"ip_address", label:"Click to Sort by IP Address", sortable:true},
            {key:"major", label:"Click to Sort by Major", sortable:true},
            {key:"year", label:"Click to Sort Year", sortable:true},
        ],
        data = data1,
        table = new Y.DataTable({
            columns: cols,
            data   : data,
            summary: "Contacts list",
            caption: "Table with simple column sorting"
        }).render("#sort");
    });
    
}

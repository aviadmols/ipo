
acf.addAction('acfe/fields/button/before/name=create_events', function($el, data){
    
        // log arguments
        console.log($el);
        console.log(data);
    
});


acf.addFilter('acfe/fields/button/data/name=create_events', function(data, $el){
    
    // add custom key
    data.custom_key = 'value';    
    
    // return
    return data;
    
});


acf.addAction('acfe/fields/button/success/name=create_events', function(response, $el, data){
    
    // json success was sent
    if(response.success){
        
        // log arguments
        console.log(response.data);
        console.log($el);
        console.log(data);
        
    }
    
});

acf.addAction('acfe/fields/button/complete/name=create_events', function(response, $el, data){
    
    // parse json response
    response = JSON.parse(response);

    // log arguments
    console.log(response);
    console.log($el);
    console.log(data);
    
});
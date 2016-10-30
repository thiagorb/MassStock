jQuery(function ($) {
    var requests = JSON.parse($('#example-requests').html());
    
    var encoders = {
        'text/csv': function (data) {
            var headersMap = {};
            data.forEach(function (item) {
                for (var key in item) {
                    headersMap[key] = true;
                }
            });
            var headers = [];
            for (var header in headersMap) {
                headers.push(header);
            }
            
            var lines = [];
            lines.push(headers.join(';'));
            
            data.forEach(function (item) {
                lines.push(
                    headers
                        .map(function (header) {
                            return item[header] || '';
                        })
                        .join(';')
                );
            });
            
            return lines.join('\n');
        },
        'application/json': function (data) {
            return JSON.stringify(data, null, 4);
        }
    };

    $('[data-request-button]').on('click', function (e) {
        e.preventDefault();
        
        var request = requests[$(this).attr('data-request-button')];
        var selectedContentType = $('[name=content_type]').val();
        
        $('[name=request_content]').val(encoders[selectedContentType](request));
    });
    
    var addResponse = function (title, cssClass, content) {
        var responseTitle = $('<div>');
        responseTitle.addClass('response__title').text(title);
    
        var responseContent = $('<pre>');
        responseContent.addClass('response__content').html(content);
        
        var newItem = $('<li>')
            .addClass('response')
            .addClass(cssClass)
            .append(responseTitle)
            .append(responseContent);
            
        $('.response-list').prepend(newItem);
        
        setTimeout(function () { newItem.css('opacity', 1) }, 0);
    };
    
    var hasRestError = function (response) {
         if (response.messages && response.messages.error) {
            return true;
         }
         
         if (response.error) {
            return true;
         }
         
         return false;
    }
    
    $('#submit_buton').on('click', function (e) {
        e.preventDefault();
        $.ajax({
            'url': $('#request_form').attr('action'),
            'data': $('#request_form').serialize(),
            'method': $('#request_form').attr('method'),
            'dataType': 'json'
        }).done(function (ajaxResponse) {
            var restResponse;
            try {
                restResponse = JSON.parse(ajaxResponse.response);
            } catch (e) {
                return addResponse('Unable to parse response', 'response--error', ajaxResponse.response);
            }
            
            if (restResponse && hasRestError(restResponse)) {
                return addResponse('REST call error', 'response--error', JSON.stringify(restResponse, null, 4));
            }
            
            if (ajaxResponse.error) {
                return addResponse('AJAX error', 'response--error', ajaxResponse.error);
            }
            
            addResponse('Success', 'response--success', JSON.stringify(restResponse, null, 4));
        }).fail(function (error) {
            addResponse('Server error', 'response--error', error.responseText);
        });
    });
});
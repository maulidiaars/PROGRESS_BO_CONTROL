/**
 * CORE OPTIMIZED FUNCTIONS
 */
 
// GLOBAL CACHE
const appCache = {
    data: {},
    get: function(key) {
        const cached = localStorage.getItem('app_cache_' + key);
        if (cached) {
            const data = JSON.parse(cached);
            if (Date.now() - data.timestamp < 300000) { // 5 minutes
                return data.value;
            }
        }
        return null;
    },
    set: function(key, value) {
        localStorage.setItem('app_cache_' + key, JSON.stringify({
            value: value,
            timestamp: Date.now()
        }));
    }
};

// DEBOUNCE FUNCTION
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// FAST AJAX WITH CACHE
function fastAjax(url, data, callback, useCache = true) {
    const cacheKey = url + JSON.stringify(data);
    
    // CHECK CACHE FIRST
    if (useCache) {
        const cached = appCache.get(cacheKey);
        if (cached) {
            if (callback) callback(cached);
            return Promise.resolve(cached);
        }
    }
    
    // FETCH NEW DATA
    return $.ajax({
        url: url,
        data: data,
        dataType: 'json',
        cache: true // Browser cache
    }).then(function(response) {
        // CACHE RESPONSE
        if (useCache && response.success) {
            appCache.set(cacheKey, response);
        }
        if (callback) callback(response);
        return response;
    });
}

// VIRTUAL SCROLL HELPER
function initVirtualScroll(tableId, loadCallback) {
    const table = document.getElementById(tableId);
    const tbody = table.querySelector('tbody');
    let isLoading = false;
    
    window.addEventListener('scroll', debounce(function() {
        const rect = table.getBoundingClientRect();
        const viewHeight = Math.max(document.documentElement.clientHeight, window.innerHeight);
        
        if (!isLoading && rect.bottom <= viewHeight + 100) {
            isLoading = true;
            loadCallback(function() {
                isLoading = false;
            });
        }
    }, 200));
}

// SIMPLE SKELETON LOADER
function showSkeleton(selector, rows = 5) {
    const html = `<tr class="skeleton-row">
        ${Array(10).fill('<td><div class="skeleton"></div></td>').join('')}
    </tr>`.repeat(rows);
    
    $(selector).html(html);
}

// FORMAT DATE FAST
function formatDateFast(dateStr) {
    if (!dateStr || dateStr.length !== 8) return dateStr;
    return dateStr.substr(0,4) + '-' + dateStr.substr(4,2) + '-' + dateStr.substr(6,2);
}

// EXPORT
window.Core = {
    cache: appCache,
    debounce: debounce,
    fastAjax: fastAjax,
    virtualScroll: initVirtualScroll,
    skeleton: showSkeleton,
    formatDate: formatDateFast
};
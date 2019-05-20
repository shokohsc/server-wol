import $ from 'jquery';

export default {
    list() {
        return $.get('/server');
    },
    read(id) {
        return $.get('/server/' + id);
    },
    wake(id) {
        return $.get('/wake/' + id);
    },
    ping(id) {
        return $.get('/ping/' + id);
    },
    create(data) {
        return $.post('/server', data);
    },
    update(data, id) {
        return $.post('/server/' + id, data);
    },
    delete(id) {
        return $.ajax({
            url: '/server/' + id,
            method: 'DELETE'
        });
    }
};

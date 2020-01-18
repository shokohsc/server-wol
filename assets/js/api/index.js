import $ from 'jquery';

const BASE_URL = '/api';

export default {
    list() {
        return $.get(BASE_URL + '/server');
    },
    read(id) {
        return $.get(BASE_URL + '/server/' + id);
    },
    wake(id) {
        return $.get(BASE_URL + '/wake/' + id);
    },
    sleep(id) {
        return $.get(BASE_URL + '/sleep/' + id);
    },
    ping(id) {
        return $.get(BASE_URL + '/ping/' + id);
    },
    parsec(id) {
        return $.get(BASE_URL + '/parsec/' + id);
    },
    create(data) {
        return $.post(BASE_URL + '/server', data);
    },
    update(data, id) {
        return $.post(BASE_URL + '/server/' + id, data);
    },
    delete(id) {
        return $.ajax({
            url: BASE_URL + '/server/' + id,
            method: 'DELETE'
        });
    }
};

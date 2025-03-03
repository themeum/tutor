export const monthNames = [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December',
];

export const weekDayNames = ['Sun', 'Mon', 'Tues', 'Wed', 'Thurs', 'Fri', 'Sat'];

export function stringToDate(_date, _format, _delimiter) {
    const formatLowerCase = _format.toLowerCase();
    const formatItems = formatLowerCase.split(_delimiter);
    const dateItems = _date.split(_delimiter);
    const monthIndex = formatItems.indexOf('mm');
    const dayIndex = formatItems.indexOf('dd');
    const yearIndex = formatItems.indexOf('yyyy');
    let month = parseInt(dateItems[monthIndex]);
    month -= 1;
    const formatedDate = new Date(dateItems[yearIndex], month, dateItems[dayIndex]);
    return formatedDate;
}

export const urlPrams = (type, val, date = null) => {
    const url = new URL(window.location.href);
    const params = url.searchParams;
    params.set(type, val);
    params.set('paged', 1);
    params.set('current_page', 1);

    if (!date) {
        params.delete('date');
    }
    return url;
};

export const translateWeekday = (weekday) => {
    const settings = wp.date?.getSettings();
    const englishWeekdays = weekDayNames;

    const index = englishWeekdays.indexOf(weekday);
    return settings?.l10n?.weekdaysShort[index] ?? weekday;
};

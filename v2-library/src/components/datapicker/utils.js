import { __ } from "@wordpress/i18n";

export const months = [
    __('January', 'tutor'),
    __('February', 'tutor'),
    __('March', 'tutor'),
    __('April', 'tutor'),
    __('May', 'tutor'),
    __('June', 'tutor'),
    __('July', 'tutor'),
    __('August', 'tutor'),
    __('September', 'tutor'),
    __('October', 'tutor'),
    __('November', 'tutor'),
    __('December', 'tutor'),
];

// Note: Keep exactly as it is.
export const weekDayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

export const weeks = [
    __('Sun', 'tutor'),
    __('Mon', 'tutor'),
    __('Tue', 'tutor'),
    __('Wed', 'tutor'),
    __('Thu', 'tutor'),
    __('Fri', 'tutor'),
    __('Sat', 'tutor'),
];

export function stringToDate(_date, _format, _delimiter) {
    const formatLowerCase = _format.toLowerCase();
    const formatItems = formatLowerCase.split(_delimiter);
    const dateItems = _date.split(_delimiter);
    const monthIndex = formatItems.indexOf('mm');
    const dayIndex = formatItems.indexOf('dd');
    const yearIndex = formatItems.indexOf('yyyy');
    let month = parseInt(dateItems[monthIndex]);
    month -= 1;
    const formattedDate = new Date(dateItems[yearIndex], month, dateItems[dayIndex]);
    return formattedDate;
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
    const index = weekDayNames.indexOf(weekday);
    return weeks[index] ?? weekday;
};

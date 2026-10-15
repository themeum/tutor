export const parseNumberOnly = (value: string, allowNegative = false, whole = false) =>
  value
    .replace(whole ? (allowNegative ? /[^0-9-]/g : /[^0-9]/g) : allowNegative ? /[^0-9.-]/g : /[^0-9.]/g, '')
    .replace(/(?!^)-/g, '')
    .replace(whole ? /\./g : /(\..*)\./g, '$1');

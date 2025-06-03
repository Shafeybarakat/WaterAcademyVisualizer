/**
 * Config
 * -------------------------------------------------------------------------------------
 * ! IMPORTANT: Make sure you clear the browser local storage In order to see the config changes in the template.
 * ! To clear local storage: (https://www.leadshook.com/help/how-to-clear-local-storage-in-google-chrome-browser/).
 */

'use strict';
/* JS global variables
 !Please use the hex color code (#000) here. Don't use rgba(), hsl(), etc
*/
window.config = {
  colors: {
    primary: (window.WA_Helpers && window.WA_Helpers.getCssVar) ? window.WA_Helpers.getCssVar('primary') : '#696cff',
    secondary: (window.WA_Helpers && window.WA_Helpers.getCssVar) ? window.WA_Helpers.getCssVar('secondary') : '#8592a3',
    success: (window.WA_Helpers && window.WA_Helpers.getCssVar) ? window.WA_Helpers.getCssVar('success') : '#71dd37',
    info: (window.WA_Helpers && window.WA_Helpers.getCssVar) ? window.WA_Helpers.getCssVar('info') : '#03c3ec',
    warning: (window.WA_Helpers && window.WA_Helpers.getCssVar) ? window.WA_Helpers.getCssVar('warning') : '#ffab00',
    danger: (window.WA_Helpers && window.WA_Helpers.getCssVar) ? window.WA_Helpers.getCssVar('danger') : '#ff3e1d',
    dark: (window.WA_Helpers && window.WA_Helpers.getCssVar) ? window.WA_Helpers.getCssVar('dark') : '#2b2c40',
    black: (window.WA_Helpers && window.WA_Helpers.getCssVar) ? window.WA_Helpers.getCssVar('pure-black') : '#22303e',
    white: (window.WA_Helpers && window.WA_Helpers.getCssVar) ? window.WA_Helpers.getCssVar('white') : '#fff',
    cardColor: (window.WA_Helpers && window.WA_Helpers.getCssVar) ? window.WA_Helpers.getCssVar('paper-bg') : '#fff',
    bodyBg: (window.WA_Helpers && window.WA_Helpers.getCssVar) ? window.WA_Helpers.getCssVar('body-bg') : '#f5f5f9',
    bodyColor: (window.WA_Helpers && window.WA_Helpers.getCssVar) ? window.WA_Helpers.getCssVar('body-color') : '#646e78',
    headingColor: (window.WA_Helpers && window.WA_Helpers.getCssVar) ? window.WA_Helpers.getCssVar('heading-color') : '#384551',
    textMuted: (window.WA_Helpers && window.WA_Helpers.getCssVar) ? window.WA_Helpers.getCssVar('secondary-color') : '#a7acb2',
    borderColor: (window.WA_Helpers && window.WA_Helpers.getCssVar) ? window.WA_Helpers.getCssVar('border-color') : '#e4e6e8'
  },
  colors_label: {
    primary: (window.WA_Helpers && window.WA_Helpers.getCssVar) ? window.WA_Helpers.getCssVar('primary-bg-subtle') : '#e7e7ff',
    secondary: (window.WA_Helpers && window.WA_Helpers.getCssVar) ? window.WA_Helpers.getCssVar('secondary-bg-subtle') : '#ebeef0',
    success: (window.WA_Helpers && window.WA_Helpers.getCssVar) ? window.WA_Helpers.getCssVar('success-bg-subtle') : '#e8fadf',
    info: (window.WA_Helpers && window.WA_Helpers.getCssVar) ? window.WA_Helpers.getCssVar('info-bg-subtle') : '#d7f5fc',
    warning: (window.WA_Helpers && window.WA_Helpers.getCssVar) ? window.WA_Helpers.getCssVar('warning-bg-subtle') : '#fff2d6',
    danger: (window.WA_Helpers && window.WA_Helpers.getCssVar) ? window.WA_Helpers.getCssVar('danger-bg-subtle') : '#ffe0db',
    dark: (window.WA_Helpers && window.WA_Helpers.getCssVar) ? window.WA_Helpers.getCssVar('dark-bg-subtle') : '#dddde0'
  },
  fontFamily: (window.WA_Helpers && window.WA_Helpers.getCssVar) ? window.WA_Helpers.getCssVar('font-family-base') : '"Public Sans", -apple-system, blinkmacsystemfont, "Segoe UI", "Oxygen", "Ubuntu", "Cantarell", "Fira Sans", "Droid Sans", "Helvetica Neue", sans-serif',
};

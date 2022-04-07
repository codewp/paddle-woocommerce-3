/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./node_modules/@wordpress/dom-ready/build-module/index.js":
/*!*****************************************************************!*\
  !*** ./node_modules/@wordpress/dom-ready/build-module/index.js ***!
  \*****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ domReady)
/* harmony export */ });
/**
 * @typedef {() => void} Callback
 *
 * TODO: Remove this typedef and inline `() => void` type.
 *
 * This typedef is used so that a descriptive type is provided in our
 * automatically generated documentation.
 *
 * An in-line type `() => void` would be preferable, but the generated
 * documentation is `null` in that case.
 *
 * @see https://github.com/WordPress/gutenberg/issues/18045
 */

/**
 * Specify a function to execute when the DOM is fully loaded.
 *
 * @param {Callback} callback A function to execute after the DOM is ready.
 *
 * @example
 * ```js
 * import domReady from '@wordpress/dom-ready';
 *
 * domReady( function() {
 * 	//do something after DOM loads.
 * } );
 * ```
 *
 * @return {void}
 */
function domReady(callback) {
  if (typeof document === 'undefined') {
    return;
  }

  if (document.readyState === 'complete' || // DOMContentLoaded + Images/Styles/etc loaded, so we call directly.
  document.readyState === 'interactive' // DOMContentLoaded fires at this point, so we call directly.
  ) {
    return void callback();
  } // DOMContentLoaded has not fired yet, delay callback until then.


  document.addEventListener('DOMContentLoaded', callback);
}
//# sourceMappingURL=index.js.map

/***/ }),

/***/ "./assets/js/dev/admin/components/Subscriptions/Pagination.jsx":
/*!*********************************************************************!*\
  !*** ./assets/js/dev/admin/components/Subscriptions/Pagination.jsx ***!
  \*********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Pagination)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);


function Pagination(_ref) {
  var pages = _ref.pages,
      page = _ref.page,
      previous = _ref.previous,
      next = _ref.next,
      disabled = _ref.disabled;

  if (1 >= pages) {
    return null;
  }

  var blur = disabled ? ' asnp-paddle-blur' : '';
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("div", {
    className: "woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination".concat(blur)
  }, 1 !== page && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("a", {
    className: "woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button",
    onClick: function onClick(e) {
      return previous(e);
    }
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Previous', 'paddle')), page !== pages && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("a", {
    className: "woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button",
    onClick: function onClick(e) {
      return next(e);
    }
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Next', 'paddle')));
}

/***/ }),

/***/ "./assets/js/dev/admin/components/Subscriptions/index.jsx":
/*!****************************************************************!*\
  !*** ./assets/js/dev/admin/components/Subscriptions/index.jsx ***!
  \****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Subscriptions)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _paddle_api_account_subscriptions__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @paddle/api/account-subscriptions */ "./assets/js/dev/api/account-subscriptions.js");
/* harmony import */ var _Pagination__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./Pagination */ "./assets/js/dev/admin/components/Subscriptions/Pagination.jsx");
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }

function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }





var columns = [{
  key: 'order-number',
  value: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Order', 'paddle')
}, {
  key: 'date',
  value: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Date', 'paddle')
}, {
  key: 'next_bill_date',
  value: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Next Date', 'paddle')
}, {
  key: 'status',
  value: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Status', 'paddle')
}, {
  key: 'total',
  value: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Total', 'paddle')
}, {
  key: 'actions',
  value: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Actions', 'paddle')
}];
function Subscriptions() {
  var _useState = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)([]),
      _useState2 = _slicedToArray(_useState, 2),
      subscriptions = _useState2[0],
      setSubscriptions = _useState2[1];

  var _useState3 = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(1),
      _useState4 = _slicedToArray(_useState3, 2),
      pages = _useState4[0],
      setPages = _useState4[1];

  var _useState5 = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(1),
      _useState6 = _slicedToArray(_useState5, 2),
      page = _useState6[0],
      setPage = _useState6[1];

  var _useState7 = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(true),
      _useState8 = _slicedToArray(_useState7, 2),
      loading = _useState8[0],
      setLoading = _useState8[1];

  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)( /*#__PURE__*/_asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
    var response;
    return regeneratorRuntime.wrap(function _callee$(_context) {
      while (1) {
        switch (_context.prev = _context.next) {
          case 0:
            _context.prev = 0;
            setLoading(true);
            _context.next = 4;
            return (0,_paddle_api_account_subscriptions__WEBPACK_IMPORTED_MODULE_2__.getAccountSubscriptions)({
              page: page
            });

          case 4:
            response = _context.sent;
            setSubscriptions(response.items && response.items.length ? response.items : []);
            setPages(null != response.pages ? response.pages * 1 : 1);
            _context.next = 12;
            break;

          case 9:
            _context.prev = 9;
            _context.t0 = _context["catch"](0);
            console.error(_context.t0);

          case 12:
            setLoading(false);

          case 13:
          case "end":
            return _context.stop();
        }
      }
    }, _callee, null, [[0, 9]]);
  })), [page]);

  var next = function next(e) {
    e.preventDefault();
    setPage(page + 1);
  };

  var previous = function previous(e) {
    e.preventDefault();
    setPage(page - 1);
  };

  var blur = loading ? ' asnp-paddle-blur' : '';
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement((react__WEBPACK_IMPORTED_MODULE_0___default().Fragment), null, 0 < subscriptions.length && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("table", {
    className: "woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table".concat(blur)
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("thead", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("tr", null, columns.map(function (_ref2) {
    var key = _ref2.key,
        value = _ref2.value;
    return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("th", {
      className: "woocommerce-orders-table__header woocommerce-orders-table__header-".concat(key),
      key: key
    }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("span", null, value));
  }))), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("tbody", null, subscriptions.map(function (subscription) {
    return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("tr", {
      className: "woocommerce-orders-table__row woocommerce-orders-table__row--status-".concat(subscription.status, " order"),
      key: subscription.order_id
    }, columns.map(function (_ref3) {
      var key = _ref3.key,
          value = _ref3.value;

      if ('total' === key) {
        return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("td", {
          className: "woocommerce-orders-table__cell woocommerce-orders-table__cell-".concat(key),
          "data-title": value,
          key: subscription.order_id + '_' + key,
          dangerouslySetInnerHTML: {
            __html: subscription[key]
          }
        });
      }

      return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("td", {
        className: "woocommerce-orders-table__cell woocommerce-orders-table__cell-".concat(key),
        "data-title": value,
        key: subscription.order_id + '_' + key
      }, 'order-number' === key && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("a", {
        href: subscription.order_url ? subscription.order_url : '#'
      }, "#", subscription.order_id), ('date' === key || 'next_bill_date' === key) && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("time", {
        dateTime: subscription[key + '_time']
      }, subscription[key]), 'actions' === key && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("a", {
        href: subscription.cancel_url ? subscription.cancel_url : '#',
        className: "woocommerce-button button",
        target: "_blank"
      }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Cancel', 'paddle')), -1 === ['order-number', 'date', 'next_bill_date', 'actions'].indexOf(key) && null != subscription[key] && subscription[key]);
    }));
  }))), 0 < subscriptions.length && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(_Pagination__WEBPACK_IMPORTED_MODULE_3__["default"], {
    pages: pages,
    page: page,
    next: next,
    previous: previous,
    disabled: loading
  }), !loading && 0 >= subscriptions.length && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("div", {
    className: "woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("a", {
    className: "woocommerce-Button button",
    href: paddleSubscriptionsData && paddleSubscriptionsData.shopUrl ? paddleSubscriptionsData.shopUrl : '#'
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Browse products', 'paddle')), (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('No order has been made yet.', 'paddle')), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("div", {
    className: "asnp-paddle-loading",
    style: {
      display: loading ? 'block' : 'none'
    }
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Loading...', 'paddle')));
}

/***/ }),

/***/ "./assets/js/dev/admin/views/App/index.jsx":
/*!*************************************************!*\
  !*** ./assets/js/dev/admin/views/App/index.jsx ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ App)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _components_Subscriptions__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../components/Subscriptions */ "./assets/js/dev/admin/components/Subscriptions/index.jsx");
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./style.scss */ "./assets/js/dev/admin/views/App/style.scss");




function App() {
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement((react__WEBPACK_IMPORTED_MODULE_0___default().Fragment), null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(_components_Subscriptions__WEBPACK_IMPORTED_MODULE_2__["default"], null));
}

/***/ }),

/***/ "./assets/js/dev/api/account-subscriptions.js":
/*!****************************************************!*\
  !*** ./assets/js/dev/api/account-subscriptions.js ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "getAccountSubscriptions": () => (/* binding */ getAccountSubscriptions)
/* harmony export */ });
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./constants */ "./assets/js/dev/api/constants.js");
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }

function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }




var getAccountSubscriptions = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
    var args,
        query,
        response,
        _args = arguments;
    return regeneratorRuntime.wrap(function _callee$(_context) {
      while (1) {
        switch (_context.prev = _context.next) {
          case 0:
            args = _args.length > 0 && _args[0] !== undefined ? _args[0] : {};
            query = '';

            if (null != args.page && !isNaN(args.page * 1) && 0 < args.page * 1) {
              query += '?page=' + args.page * 1;
            }

            _context.prev = 3;
            _context.next = 6;
            return _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0___default()({
              path: "".concat(_constants__WEBPACK_IMPORTED_MODULE_2__.API_ROOT, "/subscriptions/account").concat(query),
              method: 'GET'
            });

          case 6:
            response = _context.sent;

            if (!response) {
              _context.next = 9;
              break;
            }

            return _context.abrupt("return", response);

          case 9:
            throw new Error((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('There was an error on getting subscriptions.', 'paddle'));

          case 12:
            _context.prev = 12;
            _context.t0 = _context["catch"](3);
            throw _context.t0;

          case 15:
          case "end":
            return _context.stop();
        }
      }
    }, _callee, null, [[3, 12]]);
  }));

  return function getAccountSubscriptions() {
    return _ref.apply(this, arguments);
  };
}();

/***/ }),

/***/ "./assets/js/dev/api/constants.js":
/*!****************************************!*\
  !*** ./assets/js/dev/api/constants.js ***!
  \****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "API_ROOT": () => (/* binding */ API_ROOT)
/* harmony export */ });
var API_ROOT = '/asnp-woocommerce-paddle/v1';

/***/ }),

/***/ "./assets/js/dev/admin/views/App/style.scss":
/*!**************************************************!*\
  !*** ./assets/js/dev/admin/views/App/style.scss ***!
  \**************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "react":
/*!************************!*\
  !*** external "React" ***!
  \************************/
/***/ ((module) => {

module.exports = (function() { return this["React"]; }());

/***/ }),

/***/ "react-dom":
/*!***************************!*\
  !*** external "ReactDOM" ***!
  \***************************/
/***/ ((module) => {

module.exports = (function() { return this["ReactDOM"]; }());

/***/ }),

/***/ "@wordpress/api-fetch":
/*!*******************************************!*\
  !*** external {"this":["wp","apiFetch"]} ***!
  \*******************************************/
/***/ ((module) => {

module.exports = (function() { return this["wp"]["apiFetch"]; }());

/***/ }),

/***/ "@wordpress/i18n":
/*!***************************************!*\
  !*** external {"this":["wp","i18n"]} ***!
  \***************************************/
/***/ ((module) => {

module.exports = (function() { return this["wp"]["i18n"]; }());

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be in strict mode.
(() => {
"use strict";
/*!***************************************!*\
  !*** ./assets/js/dev/admin/index.jsx ***!
  \***************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react_dom__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react-dom */ "react-dom");
/* harmony import */ var react_dom__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react_dom__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/dom-ready */ "./node_modules/@wordpress/dom-ready/build-module/index.js");
/* harmony import */ var _views_App__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./views/App */ "./assets/js/dev/admin/views/App/index.jsx");



(0,_wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_2__["default"])(function () {
  (0,react_dom__WEBPACK_IMPORTED_MODULE_0__.render)( /*#__PURE__*/React.createElement(_views_App__WEBPACK_IMPORTED_MODULE_1__["default"], null), document.getElementById('asnp-paddle'));
});
})();

this["[modulename]"] = __webpack_exports__;
/******/ })()
;
//# sourceMappingURL=index.js.map
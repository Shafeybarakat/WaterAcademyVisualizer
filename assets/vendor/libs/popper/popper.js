/*!
 * Popper.js
 * https://popper.js.org
 *
 * Copyright JS Foundation and other contributors
 * Released under the MIT license
 * https://popper.js.org/license
 *
 * Date: 2023-02-07T10:00:00Z
 */
(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports) :
  typeof define === 'function' && define.amd ? define(['exports'], factory) :
  (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.Popper = {}));
})(this || window, (function (exports) { 'use strict';

  function getWindow(node) {
    if (node == null) {
      return window;
    }

    if (node.toString() !== '[object Window]') {
      var ownerDocument = node.ownerDocument;

      if (ownerDocument) {
        return ownerDocument.defaultView || window;
      }
    }

    return node;
  }

  function isElement(node) {
    var OwnElement = getWindow(node).Element;
    return node instanceof OwnElement || node instanceof Element;
  }

  function isHTMLElement(node) {
    var OwnHTMLElement = getWindow(node).HTMLElement;
    return node instanceof OwnHTMLElement || node instanceof HTMLElement;
  }

  function isShadowRoot(node) {
    // IE 11 has no ShadowRoot
    if (typeof ShadowRoot === 'undefined') {
      return false;
    }

    return node instanceof ShadowRoot;
  }

  function applyStyles(_ref) {
    var state = _ref.state;
    Object.keys(state.elements).forEach(function (name) {
      var style = state.styles[name] || {};
      var attributes = state.attributes[name] || {};
      var element = state.elements[name];

      // If a popper element has been removed from the DOM and is not a part of its popper state,
      // continue to the next popper element
      if (!isHTMLElement(element) || !getHTMLElement(element)) {
        return;
      }

      Object.assign(element.style, style);
      Object.keys(attributes).forEach(function (name) {
        var value = attributes[name];

        if (value === false) {
          element.removeAttribute(name);
        } else {
          element.setAttribute(name, value);
        }
      });
    });
  }

  function effect(_ref2) {
    var state = _ref2.state,
      instance = _ref2.instance,
      options = _ref2.options;
    var _options$placement = options.placement,
      placement = _options$placement === void 0 ? state.placement : _options$placement,
      _options$strategy = options.strategy,
      strategy = _options$strategy === void 0 ? state.strategy : _options$strategy,
      _options$modifiers = options.modifiers,
      modifiers = _options$modifiers === void 0 ? [] : _options$modifiers;
    var isRTL = modifiers.reduce(function (acc, cur) {
      return cur.name === 'flip' && cur.enabled && acc;
    }, false);
    var popper = state.elements.popper;
    var style = {
      position: strategy,
      left: '0',
      top: '0',
      margin: '0',
      transform: 'translate3d(' + state.x + 'px, ' + state.y + 'px, 0)'
    };

    if (isRTL) {
      style.right = '0';
      style.left = 'auto';
    }

    Object.assign(popper.style, style);
    Object.keys(state.attributes).forEach(function (name) {
      var value = state.attributes[name];
      var element = state.elements[name];

      if (value === false) {
        element.removeAttribute(name);
      } else {
        element.setAttribute(name, value);
      }
    });
  }

  var applyStyles$1 = {
    name: 'applyStyles',
    enabled: true,
    phase: 'write',
    fn: applyStyles,
    effect: effect,
    requires: ['popperOffsets']
  };

  function getBasePlacement(placement) {
    return placement.split('-')[0];
  }

  var top = 'top';
  var bottom = 'bottom';
  var right = 'right';
  var left = 'left';
  var auto = 'auto';
  var basePlacements = [top, bottom, right, left];
  var start = 'start';
  var end = 'end';
  var clippingParents = 'clippingParents';
  var viewport = 'viewport';
  var popper = 'popper';
  var reference = 'reference';
  var variationPlacements = /*#__PURE__*/basePlacements.reduce(function (acc, placement) {
    return acc.concat([placement + "-" + start, placement + "-" + end]);
  }, []);
  var placements = /*#__PURE__*/[].concat(basePlacements, [auto], variationPlacements); // modifiers that need to read the DOM

  var beforeRead = 'beforeRead';
  var read = 'read';
  var afterRead = 'afterRead'; // pure-logic modifiers

  var beforeMain = 'beforeMain';
  var main = 'main';
  var afterMain = 'afterMain'; // modifier with DOM side-effects

  var beforeWrite = 'beforeWrite';
  var write = 'write';
  var afterWrite = 'afterWrite';
  var modifierPhases = [beforeRead, read, afterRead, beforeMain, main, afterMain, beforeWrite, write, afterWrite];

  function getHTMLElement(element) {
    if (isHTMLElement(element)) {
      return element;
    }

    // if it's a SVG element, it may be inside a Shadow Root.
    var ownerDocument = element.ownerDocument;

    if (ownerDocument) {
      var _ownerDocument$docume = ownerDocument.documentElement,
        documentElement = _ownerDocument$docume === void 0 ? document.documentElement : _ownerDocument$docume;

      if (documentElement && documentElement.contains && documentElement.contains(element)) {
        return element;
      }
    }

    return null;
  }

  function getBoundingClientRect(element) {
    var rect = element.getBoundingClientRect();
    return {
      width: rect.width,
      height: rect.height,
      top: rect.top,
      right: rect.right,
      bottom: rect.bottom,
      left: rect.left,
      x: rect.left,
      y: rect.top
    };
  }

  function getLayoutRect(element) {
    var clientRect = getBoundingClientRect(element); // DOMRect objects always include the border box and do not include scrollbars.
    // The following code ensures that clientWidth and clientHeight are used for the width and height
    // of the element, this is the same behavior as `getOffsetParent`

    var width = element.offsetWidth;
    var height = element.offsetHeight;

    if (Math.abs(clientRect.width - width) <= 1) {
      width = clientRect.width;
    }

    if (Math.abs(clientRect.height - height) <= 1) {
      height = clientRect.height;
    }

    return {
      x: clientRect.left,
      y: clientRect.top,
      width: width,
      height: height
    };
  }

  function getParentNode(element) {
    if (getBasePlacement(element) === popper) {
      return element.parentNode;
    }

    if (isShadowRoot(element)) {
      return element.host;
    }

    return element.ownerDocument.documentElement;
  }

  function getComputedStyle(element) {
    return getWindow(element).getComputedStyle(element);
  }

  function getOffsetParent(element) {
    var window = getWindow(element);
    var offsetParent = element.offsetParent;

    while (offsetParent && offsetParent.nodeName !== 'BODY' && offsetParent.nodeName !== 'HTML' && getComputedStyle(offsetParent).position === 'static') {
      offsetParent = offsetParent.offsetParent;
    }

    if (!offsetParent || offsetParent.nodeName === 'BODY' || offsetParent.nodeName === 'HTML') {
      return window.document.documentElement;
    }

    return offsetParent;
  }

  function getCompositeRect(elementOrRect, offsetParent, isFixed) {
    // Simple implementation to avoid errors
    return getBoundingClientRect(elementOrRect);
  }

  // Export the Popper functionality
  exports.applyStyles = applyStyles$1;
  exports.getBasePlacement = getBasePlacement;
  exports.getBoundingClientRect = getBoundingClientRect;
  exports.getComputedStyle = getComputedStyle;
  exports.getHTMLElement = getHTMLElement;
  exports.getLayoutRect = getLayoutRect;
  exports.getOffsetParent = getOffsetParent;
  exports.getParentNode = getParentNode;
  exports.isElement = isElement;
  exports.isHTMLElement = isHTMLElement;
  exports.isShadowRoot = isShadowRoot;
  
  return exports;
}));

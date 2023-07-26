/*!
 *  File    : menu-nodes.js
 *  Version : 1.2.1
 *  Created : 04/02/2020
 *  By      : Francesc Busquets <fbusquets@xtec.cat>
 *
 *  Display the first level of the main menu in Nodes
 *  https://agora.xtec.cat/nodes
 *
 *  @source https://github.com/projectestac/agora-hacks
 *
 *  @license EUPL-1.1
 *  @licstart
 *  (c) 2000-2019 Educational Telematic Network of Catalonia (XTEC)
 *
 *  Licensed under the EUPL, Version 1.1 or -as soon they will be approved by
 *  the European Commission- subsequent versions of the EUPL (the "Licence");
 *  You may not use this work except in compliance with the Licence.
 *
 *  You may obtain a copy of the Licence at:
 *  https://joinup.ec.europa.eu/software/page/eupl
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the Licence is distributed on an "AS IS" basis, WITHOUT
 *  WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 *  Licence for the specific language governing permissions and limitations
 *  under the Licence.
 *  @licend
 */

/* global getComputedStyle  */

(function () {

  const scriptVersion = "1.3.0"
  const scriptDate = "09/Jul/2020"

  // Track the currently displayed submenu (if any)
  let currentSubMenu = null;

  // Check if there is a touch device
  let touchDevice = false;

  // Current main menu status
  let mainMenuTransformed = false;

  // Set/unset CSS attributes to an HTML element
  function setCSS(element, attributes, set = true) {
    if (element && attributes) {
      if (set)
        Object.assign(element.style, attributes);
      else
        Object.keys(attributes).forEach(key => element.style[key] = '');
    }
  }

  // Enable or disable the main menu
  function transformMainMenu(enable) {

    // Avoid repetitive transforms
    if ((enable && mainMenuTransformed) || (!enable && !mainMenuTransformed))
      return;

    // Find the main menu panel (required!)
    const mainMenu = document.querySelector('#menu-panel');
    if (!mainMenu)
      return;

    // Find the first menu element (required!)
    const firstMenuItem = mainMenu.querySelector('.main-menu-item');
    if (!firstMenuItem)
      return;

    // Find the first link
    const firstMenuLink = mainMenu.querySelector('.main-menu-link') || firstMenuItem;
    const linkColor = getComputedStyle(firstMenuLink).color;

    // Default settings
    const defaultSettings = {
      fontSize: '0.8em',
      backgroundColor: getComputedStyle(mainMenu).backgroundColor,
      textTransform: 'uppercase',
      menuSeparator: `2px solid ${linkColor}`,
      menuBorderBottom: `2px solid ${linkColor}`,
      submenuBorder: `1px solid ${linkColor}`,
      submenuTextTransform: 'none',
    };

    // Customized settings should be defined in a global object named "NODES_MENU_SETTINGS"
    const settings = Object.assign({}, defaultSettings, window.NODES_MENU_SETTINGS);

    // Floating submenus
    const floatAttr = {
      position: 'absolute',
      'z-index': 100,
      padding: '1rem',
      border: settings.submenuBorder,
      'background-color': settings.backgroundColor,
      display: 'none',
      'text-transform': settings.submenuTextTransform,
    };

    // Main menu items
    const itemAttr = {
      'margin-left': '1em',
      'padding-right': '1em',
      'padding-bottom': 0,
      'margin-bottom': '10px',
      'text-transform': settings.textTransform,
      'border-right': settings.menuSeparator,
      'font-size': settings.fontSize
    };

    // Main panel
    const panelAttr = {
      display: 'inline-block',
      'padding-bottom': 0,
      'padding-left': 0,
      'border-bottom': settings.menuBorderBottom,
    };

    // Any click outside menu items hide submenu
    if (enable) {
      mainMenu.addEventListener('click', hideSubMenu);
      mainMenu.addEventListener('mouseleave', hideSubMenu);
    }
    else {
      mainMenu.removeEventListener('click', hideSubMenu);
      mainMenu.removeEventListener('mouseleave', hideSubMenu);
    }

    mainMenu.querySelectorAll('.main-menu-item').forEach((element, n, listObj) => {
      // Add or remove event listeners
      if (enable) {
        element.addEventListener('touchstart', handleTouchStart);
        element.addEventListener('mouseenter', handleMouseEnter);
        element.addEventListener('click', handleMouseClick);
      }
      else {
        element.removeEventListener('touchstart', handleTouchStart);
        element.removeEventListener('mouseenter', handleMouseEnter);
        element.removeEventListener('click', handleMouseClick);
      }
      // Set CSS attributes
      setCSS(element, itemAttr, enable);
      // Remove last separator
      if (n === listObj.length - 1)
        element.style['border-right'] = 'inherit';
      // Float or unfloat the submenu, if any
      setCSS(element.querySelector('.sub-menu'), floatAttr, enable);
    });
    // Set CSS attributes to the main menu
    setCSS(mainMenu, panelAttr, enable);
    if (!enable)
      mainMenu.style.display = "none";
    // Set the 'transformed' flag
    mainMenuTransformed = enable;
  }

  // Hide current submenu and replace it by another one (when not null)
  function showSubMenu(menu) {
    if (currentSubMenu) {
      setElementVisible(currentSubMenu, false);
      currentSubMenu = null;
    }
    if (menu) {
      currentSubMenu = menu;
      setElementVisible(currentSubMenu, true);
    }
  }

  // Hide current submenu
  function hideSubMenu() {
    showSubMenu(null);
  }

  // Show/hide an element
  function setElementVisible(element, enable) {
    element.style.display = enable ? '' : 'none';
  }

  // Detect if this is a touch device
  function handleTouchStart() {
    touchDevice = true;
  }

  // Handle mouse enter events
  function handleMouseEnter(ev) {
    ev.preventDefault();
    // Don't process mouseenter on touch devices
    if (touchDevice)
      return;
    // Get the event target
    let el = ev.target;
    // The click can be originated by a children, so find the closest "li" parent
    while (el && !el.classList.contains('main-menu-item'))
      el = el.parentElement;
    // Show new submenu, if any
    showSubMenu(el.querySelector('.sub-menu'));
  }

  // Handle mouse click events
  function handleMouseClick(ev) {
    let el = ev.target;
    // The click can be originated by a children, so find the closest "li" parent
    while (el && !el.classList.contains('main-menu-item'))
      el = el.parentElement;
    // Continue only if element was found
    if (el) {
      // Does this element have an associated submenu?
      const subMenu = el.querySelector('.sub-menu');
      // No associated submenu exists, or exists and is already open?
      if (!subMenu || (currentSubMenu && currentSubMenu.isEqualNode(subMenu)))
        // Let's the event run (maybe it was originated on a link)
        return;
      // Cancel event propagation
      ev.preventDefault();
      ev.stopImmediatePropagation();
      // Show new submenu, if any
      showSubMenu(subMenu);
    }
  }

  // Expose to global
  window.transformMainMenu = transformMainMenu;

  // Log date and version 
  console.log(`Loaded "menu-nodes.js" v${scriptVersion} (${scriptDate})`);

})();

// Actions to be performed at startup
window.addEventListener('load', () => {
  // Show the main menu
  transformMainMenu(true);
  // Click on the "Menu" button will reset the main menu
  const menuButton = document.querySelector('button[title="MENU"]');
  if (menuButton)
    menuButton.addEventListener('click', () => transformMainMenu(false));
});

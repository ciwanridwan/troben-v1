const layout = {
  mode: 'side',
  is_mobile: false,
  width: 'fixed',                 // value: fixed, fluid
  theme: 'light',                  // value: dark, light
  aside: {
    collapse: true,
    fixed: true,
    theme: 'light'               // value: dark, light
  },
  header: {
    fixed: true,
    theme: 'light'              // value: dark, light
  },
  toggleAside() {
    this.aside.fixed = !this.aside.fixed
  },
  toggleCollapse() {
    this.aside.collapse = !this.aside.collapse
  }
}

export default layout

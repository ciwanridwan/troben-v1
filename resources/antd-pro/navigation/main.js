const main = {
  dashboard: {
    icon: 'dashboard',
    text: 'Dashboard',
    route: 'app.dashboard',
    children: null,
    shortKey: ['ctrl', 'alt', 'd']
  },
  discover: {
    icon: 'star',
    text: "Discover",
    route: 'app.discover',
    children: null,
    shortKey: ['ctrl', 'alt', 'o']
  },
  patient: {
    icon: 'snippets',
    text: 'Patient Registry',
    route: 'app.patient',
    children: null,
    shortKey: ['ctrl', 'alt', 'p']
  },
  share: {
    icon: 'share-alt',
    text: 'Share',
    route: 'app.share',
    children: null,
    shortKey: ['ctrl', 'alt', 's']
  },
  setting: {
    icon: 'setting',
    text: 'Setting',
    route: 'app.setting',
    children: {
      center: {
        icon: 'bank',
        text: 'Center',
        route: 'app.setting.center',
      },
      physician: {
        icon: 'medicine-box',
        text: 'Physician',
        route: 'app.setting.physician'
      }
    }
  }
}

export default main

// --
let protocol = window.location.protocol
let host = window.location.hostname
// --
const path = window.location.pathname
const marker = '/gliese/'
const idx = path.toLowerCase().indexOf(marker)
const basePath = idx >= 0 ? path.substring(0, idx + marker.length) : '/'
const BASE_URL = protocol + '//' + host + basePath

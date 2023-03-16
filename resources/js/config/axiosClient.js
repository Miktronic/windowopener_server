import axios from 'axios';
import constants from './constants';


export const axiosClient = axios.create();

axiosClient.defaults.baseURL = constants.HOST_URL;

var token = '';
if(token = localStorage.getItem('token')){
  axiosClient.defaults.headers = {
    ...constants.headers,
  Authorization: token };
}

// To share cookies to cross site domain, change to true.
axiosClient.defaults.withCredentials = false;

export function getCustomRequest(URL) {
  return axiosClient.get(`/${URL}`).then(response => response);
}

export function getRequest(URL) {
  return axiosClient.get(`/${URL}`).then(response => response);
}

export function postRequest(URL, payload) {
  return axiosClient.post(`/${URL}`, payload).then(response => response);
}

export function patchRequest(URL, payload) {
  return axiosClient.patch(`/${URL}`, payload).then(response => response);
}

export function deleteRequest(URL) {
  return axiosClient.delete(`/${URL}`).then(response => response);
}

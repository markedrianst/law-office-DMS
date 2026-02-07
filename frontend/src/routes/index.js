import { createRouter, createWebHistory } from "vue-router";
import Login from "@/pages/Login.vue";
import Dashboard from "@/pages/Dashboard.vue";

const routes = [
  {
    path: "/",
    component: Login,
    meta: { layout: "auth" },
  },
  {
    path: "/dashboard",
    component: Dashboard,
    meta: { layout: "main" },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;

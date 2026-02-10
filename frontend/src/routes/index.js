import { createRouter, createWebHistory } from "vue-router";

// Pages
import Login from "@/pages/Authentication/Login.vue";
import Register from "@/pages/Authentication/Register.vue";
import AdminDashboard from "@/pages/Admin/Admindashboard.vue";
import StaffDashboard from "@/pages/Staff/Staffdashboard.vue";
import LawyerDashboard from "@/pages/Lawyer/Lawyerdashboard.vue";
import Clients from "@/pages/Staff/Clients.vue";
import Settings from "@/pages/Staff/Setting.vue";

const routes = [
  { path: "/", component: Login },
  { path: "/register", component: Register },
  { path: "/clients", component: Clients, meta: { requiresAuth: true, role: "staff" } },
  { path: "/settings", component: Settings, meta: { requiresAuth: true, role: "staff" } },
  { path: "/admindashboard", component: AdminDashboard, meta: { requiresAuth: true, role: "admin" } },
  { path: "/staffdashboard", component: StaffDashboard, meta: { requiresAuth: true, role: "staff" } },
  { path: "/lawyerdashboard", component: LawyerDashboard, meta: { requiresAuth: true, role: "lawyer" } },
  { path: "/:pathMatch(.*)*", redirect: "/" },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Auth & role guard
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem("token");
  const user = JSON.parse(localStorage.getItem("user") || "{}");

  // If route requires auth but no token
  if (to.meta.requiresAuth && !token) return next("/");

  // If route requires role but user has a different role
  if (to.meta.requiresAuth && to.meta.role && user.role !== to.meta.role) {
    // Redirect to correct dashboard
    if (user.role === "admin") return next("/admindashboard");
    if (user.role === "staff") return next("/staffdashboard");
    if (user.role === "lawyer") return next("/lawyerdashboard");
    return next("/"); // fallback
  }

  // If visiting login page while logged in
  if ((to.path === "/" || to.path === "/register") && token) {
    if (user.role === "admin") return next("/admindashboard");
    if (user.role === "staff") return next("/staffdashboard");
    if (user.role === "lawyer") return next("/lawyerdashboard");
  }

  next();
});

export default router;

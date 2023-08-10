import { createRouter, createWebHistory } from "vue-router";

const routes = [
    {
      path: "/",
      name: "list.room",
      component: () => import(/* webpackChunkName: "list.room" */ "../pages/ListRoom.vue"),
    },
    {
      path: "/rooms/:roomId",
      name: "room",
      component: () => import(/* webpackChunkName: "room" */ "../pages/Room.vue"),
    },
    // {
    //   path: "/logout",
    //   name: "logout",
    //   component: () => import(/* webpackChunkName: "logout" */ "../views/logout.vue"),
    // },
  ];
  
  const router = createRouter({
    history: createWebHistory(),
    routes,
  });
  
  export default router;
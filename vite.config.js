import { defineConfig } from "vite";
import path from "path";
import copy from "rollup-plugin-copy";

export default defineConfig({
  root: "./assets",
  base: "/build/",
  build: {
    assetsDir: "",
    outDir: "../public/build/",
    rollupOptions: {
      input: {
        "script.js": path.resolve(__dirname, "assets/js/script.js"),
        "style.css": path.resolve(__dirname, "assets/css/style.css"),
      },
      plugins: [
        copy({
          targets: [
            { src: "public/assets/js/*", dest: "assets/js" },
            { src: "public/assets/css/*", dest: "assets/css" },
          ],
          hook: "buildStart",
          copyOnce: true,
        }),
      ],
    },
  },
});

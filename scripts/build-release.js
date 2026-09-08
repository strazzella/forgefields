const fs = require("fs");
const path = require("path");
const { execSync } = require("child_process");

const root = path.resolve(__dirname, "..");
const distDir = path.join(root, "dist");
const pluginDir = path.join(distDir, "forge-fields");
const zipPath = path.join(distDir, "forge-fields.zip");

const releaseItems = [
  "assets",
  "includes",
  "forge-fields.php",
  "uninstall.php",
  "readme.txt",
  "LICENSE",
];

function removeIfExists(target) {
  if (fs.existsSync(target)) {
    fs.rmSync(target, {
      recursive: true,
      force: true,
    });
  }
}

function copyItem(source, destination) {
  fs.cpSync(source, destination, {
    recursive: true,
  });
}

console.log("Building Forge Fields production assets...");

execSync("npm run build", {
  cwd: root,
  stdio: "inherit",
});

console.log("Creating clean release directory...");

removeIfExists(distDir);

fs.mkdirSync(pluginDir, {
  recursive: true,
});

for (const item of releaseItems) {
  const source = path.join(root, item);
  const destination = path.join(pluginDir, item);

  if (!fs.existsSync(source)) {
    throw new Error(`Required release item is missing: ${item}`);
  }

  copyItem(source, destination);
}

console.log("Creating forge-fields.zip...");

if (process.platform === "win32") {
  execSync(
    `powershell -NoProfile -Command "Compress-Archive -Path '${pluginDir}' -DestinationPath '${zipPath}' -Force"`,
    {
      cwd: root,
      stdio: "inherit",
    },
  );
} else {
  execSync(`cd "${distDir}" && zip -rq "forge-fields.zip" "forge-fields"`, {
    cwd: root,
    stdio: "inherit",
  });
}

removeIfExists(pluginDir);

console.log("");
console.log("Release complete:");
console.log(zipPath);

const fs = require('fs');

const content = fs.readFileSync('script.js', 'utf8');

const newProjects = [
    {
        id: 1718863121001,
        title: "Custom Industrial Conveyors",
        description: "High-quality stainless steel conveyors designed for automated assembly lines.",
        type: "image",
        url: "11.jpeg"
    },
    {
        id: 1718863121002,
        title: "Automated Assembly Lines",
        description: "Precision engineered assembly lines for seamless production processes.",
        type: "image",
        url: "22.jpeg"
    },
    {
        id: 1718863121003,
        title: "Robotics & Precision Machinery",
        description: "Advanced robotic solutions tailored for modern industrial applications.",
        type: "image",
        url: "33.jpeg"
    },
    {
        id: 1718863121004,
        title: "Heavy Duty Steel Fabrication",
        description: "Durable steel structures complying with strict industry standards.",
        type: "image",
        url: "44.jpeg"
    },
    {
        id: 1718863121005,
        title: "CNC Machining Solutions",
        description: "State-of-the-art CNC machining for custom engineering components.",
        type: "image",
        url: "55.jpeg"
    },
    {
        id: 1718863121006,
        title: "Smart Automation Systems",
        description: "Intelligent automation frameworks that increase operational efficiency.",
        type: "image",
        url: "66.jpeg"
    },
    {
        id: 1718863121007,
        title: "Hydraulic Press Equipment",
        description: "Reliable hydraulic systems built for heavy manufacturing demands.",
        type: "image",
        url: "77.jpeg"
    },
    {
        id: 1718863121008,
        title: "Quality Control Systems",
        description: "Comprehensive QA machines to ensure product reliability and excellence.",
        type: "image",
        url: "88.jpeg"
    }
];

const startMarker = 'const defaultProjects = ';
const endMarker = ';\n\nconst defaultSettings = ';

const startIndex = content.indexOf(startMarker);
const endIndex = content.indexOf('const defaultSettings = ');

if (startIndex !== -1 && endIndex !== -1) {
    const before = content.substring(0, startIndex + startMarker.length);
    // Find the end of the array before defaultSettings
    const after = content.substring(endIndex);
    const newData = JSON.stringify(newProjects, null, 4);
    
    fs.writeFileSync('script.js', before + newData + ';\n\n' + after);
    console.log("Successfully updated projects!");
} else {
    console.log("Could not find markers.", startIndex, endIndex);
}

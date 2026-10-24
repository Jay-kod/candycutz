import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import TestimonialsMarquee from '../TestimonialsMarquee.vue';

describe('TestimonialsMarquee', () => {
  it('renders section title and client stories badge', () => {
    const wrapper = mount(TestimonialsMarquee, {
      props: {
        title: 'What our clients say',
        badge: 'Client stories',
      },
    });

    expect(wrapper.text()).toContain('Client stories');
    expect(wrapper.text()).toContain('What our clients say');
  });

  it('renders default testimonials when no testimonials are passed', () => {
    const wrapper = mount(TestimonialsMarquee, {
      props: {
        testimonials: [],
      },
    });

    expect(wrapper.text()).toContain('Guillermo Rauch');
    expect(wrapper.text()).toContain('x.com');
    expect(wrapper.findAll('.marquee-track').length).toBe(2);
  });

  it('renders custom testimonials in infinite tracks', () => {
    const customTestimonials = [
      {
        id: 'test-1',
        client_name: 'John Doe',
        role: 'Verified Customer',
        source: 'x.com',
        review: 'Incredible skin fade and sharp beard line!',
      },
    ];

    const wrapper = mount(TestimonialsMarquee, {
      props: {
        testimonials: customTestimonials,
      },
    });

    expect(wrapper.text()).toContain('John Doe');
    expect(wrapper.text()).toContain('Incredible skin fade and sharp beard line!');
    expect(wrapper.find('[aria-hidden="true"]').exists()).toBe(true);
  });
});

